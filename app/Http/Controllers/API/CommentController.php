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
use Illuminate\Support\Facades\Log;
use App\Events\UpdateComment;
use App\Events\DeleteComment;

class CommentController extends Controller
{
    /**
     * Insert comment
     */
    public function comment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator,
            ]);
        }
        try {
            $data = Comment::create([
                'userid' => auth()->user()->id,
                'postid' => $id,
                'comment' => $request->comment,
                'created_by' => auth()->user()->id,
            ]);

            $user = User::where('id', $data['userid'])->first();

            if (!empty(auth()->user()->token)) {
                event(new CreateComment($data, $user));
            }

            return response()->json(['success' => 'Comment posted successfully']);
        } catch (QueryException $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong'
            ]);
        }
    }

    /**
     * Update comment
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'comment' => 'required',
            ]);
            if ($validator->fails())
                return response()->json([
                    'success' => false,
                    'message' => $validator,
                ]);

            Comment::where('userid', auth()->user()->id)
                ->where('id', $id)
                ->update([
                    'comment' => $request->comment,
                ]);

            $data = Comment::where('userid', auth()->user()->id)
                ->where('id', $id)
                ->first();

            $user = User::where('id', auth()->user()->id)
                ->first();

            if (!empty(auth()->user()->token))
                event(new UpdateComment($data, $user));

            return response()->json([
                'success' => 'Comment updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong'
            ]);
        }
    }

    /**
     * List post-comments (hasMany)
     */
    public function show($id)
    {
        try {
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Post::where('userid', auth()->user()->id)
                    ->where('id', $id)
                    ->with('comments')
                    ->get();
            } else {
                $data = Post::where('id', $id)
                    ->with('comments')
                    ->get();
            }

            return collect($data)->transformWith(new PostListTransformer());
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong'
            ]);
        }
    }

    /**
     * List comments-post (Inverse of hasMany)
     */
    public function showlist($id)
    {
        try {
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = Comment::where('userid', auth()->user()->id)
                    ->where('postid', $id)
                    ->with('post')
                    ->get();
            } else {
                $data = Comment::where('postid', $id)
                    ->with('post')
                    ->get();
            }

            return collect($data)->transformWith(new CommentTransformer());
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $data = Comment::where('id', $id)->first();
            $user = User::where('id', auth()->user()->id)->first();

            if (!empty(auth()->user()->token))
                event(new DeleteComment($data, $user));

            $data = Comment::where('id', $id)->delete();

            return response()->json([
                'success' => 'Comment successfully deleted'
            ]);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong'
            ]);
        }
    }
}
