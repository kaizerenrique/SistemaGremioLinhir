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
    public function handle(Request $request)
    {
        // Validar el token
        $request->validate([
            'access_token' => 'required|string'
        ]);

        try {
            // Obtener usuario de Discord
            $discordUser = Socialite::driver('discord')->userFromToken($request->access_token);

            // Generar contraseña aleatoria
            $plainPassword = Str::password(12); // 12 caracteres con combinación de letras, números y símbolos
            
            // Enviar correo con la contraseña en texto plano
            Mail::to($providerUser->getEmail())->send(new PasswordGeneratedMail($plainPassword));
            
            // Buscar o crear usuario
            $user = User::firstOrCreate(
                ['discord_id' => $discordUser->getId()],
                [
                    'name' => $discordUser->getName(),
                    'email' => $discordUser->getEmail(),
                    'password' => Hash::make($plainPassword ), // Contraseña dummy
                ]
            );

            // Generar token de API
            $token = $user->createToken('discord-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
