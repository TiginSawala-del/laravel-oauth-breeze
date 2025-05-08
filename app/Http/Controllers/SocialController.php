<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function loginWithGoogle()
    {

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $existingUser = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($existingUser) {
                if ($existingUser->google_id !== $googleUser->id) {
                    $existingUser->google_id = $googleUser->id;
                    $existingUser->save();
                }
                Auth::login($existingUser);
            } else {
                $createUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt('password')
                ]);

                Auth::login($createUser);
            }
            return redirect()->to('/dashboard');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
