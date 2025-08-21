<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordGeneratedMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SocialiteAuthController extends Controller
{
    

    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback()
    {
        $providerUser = Socialite::driver('discord')->user();
        $user = User::where('email', $providerUser->getEmail())->first();
        
        //comprobar si el usuario está registrado
        if (!$user) {
            // Generar contraseña aleatoria
            $plainPassword = Str::password(12); // 12 caracteres con combinación de letras, números y símbolos
            
            

            $user = User::create([
                'email' => $providerUser->getEmail(),
                'name' => $providerUser->getName(),
                'password' => Hash::make($plainPassword),
            ])->assignRole('Usuario');


            $nombre = $providerUser->getName();
            // Enviar correo con la contraseña en texto plano
            Mail::to($providerUser->getEmail())->send(new PasswordGeneratedMail($plainPassword, $nombre ));
        }

        $user->authProviders()->updateOrCreate([
            'provider' => 'discord',
        ], [
            'provider_id' => $providerUser->getId(),
            'avatar' => $providerUser->getAvatar(),
            'token' => $providerUser->token,
            'nickname' => $providerUser->getNickname(),
            'login_at' => Carbon::now(),
        ]);

        Auth::login($user);

        return redirect('/dashboard');
        
    }
}
