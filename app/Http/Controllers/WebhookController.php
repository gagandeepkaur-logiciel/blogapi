<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Redirect 
     */
    public function backFromWebHook(Request $request)
    {
        Log::info($request);
        // try {
            // $userid = Auth::user()->id;
        // print_r($userid);
        //     // $data = Post::create([
        //     //     'userid' => $userid,
        //     //     'categoryid' => 4,
        //     //     'description' => 'description',
        //     //     'title' => $request->message,
        //     //     'image' => $request->full_picture,
        //     //     'facebook_post_id' => $request->id,
        //     //     'created_by' => $userid,
        //     // ]);
           
           
        // } catch (\Exception $e) {
        //     return $e->getMessage();
        // }
    }


}
