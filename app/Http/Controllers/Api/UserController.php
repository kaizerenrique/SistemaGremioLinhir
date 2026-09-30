<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordGeneratedMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    /**
     * Redirección para API (stateless, redirect_uri de API).
     */
    public function redirect_api()
    {
        return Socialite::driver('discord')
            ->redirectUrl(config('services.discord.redirect_api'))
            ->stateless()
            ->redirect();
    }

    /**
     * Callback OAuth de Discord para la API.
     * Crea o recupera al usuario, vincula el proveedor
     * y devuelve un token Sanctum para el cliente móvil.
     */
    public function callback_api(Request $request): JsonResponse
    {
        // ---------------------------------------------------------
        // 1. Obtener el usuario de Discord
        // ---------------------------------------------------------
        try {
            $providerUser = Socialite::driver('discord')
                ->redirectUrl(config('services.discord.redirect_api'))
                ->stateless()
                ->user();
        } catch (\Throwable $e) {
            Log::warning('callback_api: fallo obteniendo usuario de Discord', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Autenticación con Discord fallida'], 401);
        }

        $email = $providerUser->getEmail();

        // ---------------------------------------------------------
        // 2. Discord puede no entregar email
        // ---------------------------------------------------------
        if (empty($email)) {
            Log::warning('callback_api: Discord no devolvió email', [
                'discord_id' => $providerUser->getId(),
            ]);
            return response()->json([
                'error' => 'Discord no entregó un correo. Autoriza el scope "email" o verifica tu correo en Discord.',
            ], 422);
        }

        // ---------------------------------------------------------
        // 3. Buscar o crear el usuario
        // ---------------------------------------------------------
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $plainPassword = Str::password(12);

                // Crear el usuario y asignar el rol base
                $user = User::create([
                    'email'             => $email,
                    'name'              => $providerUser->getName() ?: $providerUser->getNickname() ?: 'Usuario Discord',
                    'password'          => Hash::make($plainPassword),
                    'email_verified_at' => now(), // Discord ya validó el correo
                ]);

                $user->assignRole('Usuario');

                // Enviar credenciales al correo
                Mail::to($email)->send(new PasswordGeneratedMail($plainPassword, $user->name));
            }
        } catch (\Throwable $e) {
            Log::error('callback_api: fallo creando o recuperando usuario', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'No se pudo crear la cuenta'], 500);
        }

        // ---------------------------------------------------------
        // 4. Vincular (o actualizar) el proveedor Discord
        // ---------------------------------------------------------
        try {
            $user->authProviders()->updateOrCreate(
                ['provider' => 'discord'],
                [
                    'provider_id' => $providerUser->getId(),
                    'avatar'      => $providerUser->getAvatar(),
                    'token'       => $providerUser->token,
                    'nickname'    => $providerUser->getNickname(),
                    'login_at'    => Carbon::now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('callback_api: fallo vinculando proveedor', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            // No abortamos: el usuario existe y podemos darle token igual.
        }

        // ---------------------------------------------------------
        // 5. Emitir token Sanctum
        // ---------------------------------------------------------
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'roles'             => $user->getRoleNames(),
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    /**
     * Devuelve el usuario autenticado (por token Sanctum).
     */
    public function getUser(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Cierra la sesión del dispositivo actual (revoca solo su token).
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
