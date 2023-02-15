<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $apitoken = $success['token'] = $user->createToken('postman')->accessToken;
            DB::table('users')->where('id', $user->id)->update([
                'api_token' => $apitoken,
                ]);
            $success['id'] = $user->id;
            $success['name'] = $user->name;
            return response()->json(['success' => $success]);
        }
    }


    public function logged_out(Request $request)
    {
        try {
            $user = Auth::user()->token();
            $user->revoke();
            // Socialite::driver('facebook')->stateless()->user();
            return response()->json(['success' => true, 'message' => 'User logged out!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}