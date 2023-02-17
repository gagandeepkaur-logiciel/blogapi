<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Transformers\CommentTransformer;
use App\Transformers\PostListTransformer;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Events\CreateComment;
use Illuminate\Database\QueryException;

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
                $create_comment = Comment::create([
                    'userid' => $userid,
                    'postid' => $id,
                    'comment' => $comment,
                    'created_by' => $userid,
                ]);

                $user = User::where('id', $create_comment['userid'])->get();

                if (!empty(auth()->user()->token)){
                    CreateComment::dispatch([$create_comment, $user]);
                }

                return (['success' => $create_comment]);
        } catch (QueryException $e) {
            return response()->json('Cannot add or update a child row');
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
