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
    /**
     * Insert comment
     */
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
                $postid = Post::where('id', $id)
                ->pluck('facebook_post_id')[0];
                $accesstoken = auth()->user()->token;
                $facebook_user_id = auth()->user()->facebook_id;
                $profileresponse = Http::get(env('FACEBOOK_GRAPH_API').'me/accounts?access_token=' . $accesstoken . '');
                $pagetoken = $profileresponse['data'][0]['access_token'];
                $pageid = $profileresponse['data'][0]['id'];
                $feedresponse = Http::get(env('FACEBOOK_GRAPH_API'). $pageid . '/feed?&access_token=' . $pagetoken . '');
                $commentresponse = Http::post(env('FACEBOOK_GRAPH_API'). $postid . '/comments/?message=' . $comment . '&access_token=' . $pagetoken . '');
                $data = Comment::create([
                    'userid' => $userid,
                    'postid' => $id,
                    'comment' => $comment,
                    'facebook_post_id' => $postid,
                    'comment_id' => $commentresponse['id'],
                    'pageid' => $pageid,
                    'created_by' => $userid,
                ]);

                return $commentresponse->json(['success' => 'Data created successfully']);
            }else{
                $data = Comment::create([
                    'userid' => $userid,
                    'postid' => $id,
                    'comment' => $comment,
                    'created_by' => $userid,
                ]);

                return $data->json(['success' => 'Data created successfully']);
            }
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * List post-comments
     */
    public function show($id)
    {
        try {
            $userid = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Post::where('userid', $userid)
                ->where('id', $id)
                ->with('comments')
                ->get();
            } else {
                $data = Post::where('id', $id)
                ->with('comments')
                ->get();
            }

            return collect($data)->transformWith(new PostListTransformer());
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * List comments-post (Inverse of hasMany)
     */
    public function showlist($id)
    {
        try {
            $userid = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Comment::where('userid', $userid)
                ->where('postid', $id)
                ->with('post')
                ->get();
            } else {
                $data = Comment::where('postid', $id)
                ->with('post')
                ->get();
            }

            return collect($data)->transformWith(new CommentTransformer());
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }
    }
}
