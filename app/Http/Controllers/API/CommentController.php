<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Transformers\CommentTransformer;
use App\Transformers\PostListTransformer;
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

    public function show($id)
    {
        try {
            $userid = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Post::where('userid', $userid)->where('id', $id)->with('comments')->get();
            } else {
                $data = Post::where('id', $id)->with('comments')->get();
            }
            return collect($data)->transformWith(new PostListTransformer());
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function showlist($id)
    {
        try {
            $userid = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Comment::where('userid', $userid)->where('postid', $id)->with('post')->get();
            } else {
                $data = Comment::where('postid', $id)->with('post')->get();
            }
            return collect($data)->transformWith(new CommentTransformer());
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }
    }

    function list($id) {
        try {
            $userid = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Post::where('userid', $userid)->where('id', $id)->with('comments')->get();
            } else {
                $data = Post::where('id', $id)->with('comments')->get();
            }
            return collect($data)->transformWith(new PostListTransformer());
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
