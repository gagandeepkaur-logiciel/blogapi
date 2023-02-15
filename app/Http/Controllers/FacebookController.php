<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function loginUsingFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callbackFromFacebook()
    {
            try {
            $user = Socialite::driver('facebook')->user();
            $saveUser = User::updateOrCreate(
                [
                    'email' => $user->getEmail(),
                ],
                [
                    'facebook_id' => $user->id,
                    'token' => $user->token,
                    'facebook_user' => 'true',
                ]
            );

            Auth::login($saveUser, true);

            return response()->json(['success' => $user->user['id']]);
            // return redirect('/');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function backFromWebHook(){
        echo "yes";
    }
}
