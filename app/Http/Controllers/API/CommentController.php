<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function comment(Request $request, $id)
    {
        $userid = auth()->user()->id;
        $comment = $request->comment;
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try {
            if (!empty(auth()->user()->token)) {
                $postid = Post::where('id', $id)->pluck('facebook_post_id')[0];
                // dd($postid);
                $accesstoken = auth()->user()->token;
                $facebook_user_id = auth()->user()->facebook_id;
                $res = Http::get('https://graph.facebook.com/v15.0/me/accounts?access_token=' . $accesstoken . '');
                // return $res->json();
                $pagetoken = $res['data'][0]['access_token'];
                $pageid = $res['data'][0]['id'];
                $response = Http::get('https://graph.facebook.com/v15.0/' . $pageid . '/feed?&access_token=' . $pagetoken . '');
                $r = Http::post('https://graph.facebook.com/v15.0/' . $postid . '/comments/?message=' . $comment . '&access_token=' . $pagetoken . '');
                $data = Comment::create([
                    'userid' => $userid,
                    'postid' => $id,
                    'comment' => $comment,
                    'facebook_post_id' => $postid,
                    'comment_id' => $r['id'],
                    'pageid' => $pageid,
                    'created_by' => $userid,
                ]);
                return $r->json();
            }
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}