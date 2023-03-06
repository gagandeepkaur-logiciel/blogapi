<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\FacebookPage;

class FacebookController extends Controller
{
    /**
     * Login through facebook 
     * authentication
     */
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

            // return response()->json(['success' => $user->user['id']]);
            return redirect('page_tokens');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function get_tokens_from_facebook()
    {
        try {
            $access_token = auth()->user()->token;
            $user_id = auth()->user()->id;
            $facebook_id = User::where('id', $user_id)->pluck('facebook_id')->first();
            $response = Http::get(env('FACEBOOK_GRAPH_API') . 'me/accounts?access_token=' . $access_token);
            $count = count($response['data']);

            foreach ($response['data'] as $count) {
                $data = FacebookPage::create([
                    'user_id' => auth()->user()->id,
                    'facebook_id' => $facebook_id,
                    'page_id' => $count['id'],
                    'page_name' => $count['name'],
                    'access_token' => $count['access_token'],
                ]);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
