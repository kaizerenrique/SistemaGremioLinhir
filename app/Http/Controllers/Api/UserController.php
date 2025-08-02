<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordGeneratedMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Redirección para API
    public function redirect_api()
    {
        return Socialite::driver('discord')
            ->redirectUrl(config("services.discord.redirect_api"))
            ->stateless()
            ->redirect();

            //dd($prueba);
        
    }

    // Callback para API
    public function callback_api(Request $request)
    {
        try {
            $providerUser = Socialite::driver('discord')->redirectUrl(config("services.discord.redirect_api"))->stateless()->user();
            //comprobar si el usuario está registrado
            $user = User::where('email', $providerUser->getEmail())->first();

            if (!$user) {
                // Generar contraseña aleatoria
                $plainPassword = Str::password(12); // 12 caracteres con combinación de letras, números y símbolos
                
                // Enviar correo con la contraseña en texto plano
                Mail::to($providerUser->getEmail())->send(new PasswordGeneratedMail($plainPassword));

                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'password' => Hash::make($plainPassword),
                ]);
            }

            $user->authProviders()->updateOrCreate([
                'provider' => 'discord',
            ], [
                'provider_id' => $providerUser->getId(),
                'avatar' => $providerUser->getAvatar(),
                'token' => $providerUser->token,
                'nickname' => $providerUser->getNickname(),
            ]);

            // Crear token de API
            $token = $user->createToken('mobile-token')->plainTextToken;

            return response()->json([
                    'token' => $token,
                    'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Autenticación fallida'], 401);
        }        

    }

    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
