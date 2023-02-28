<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Transformers\PostListTransformer;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\CreatePost;
use App\Models\User;
use App\Events\UpdatePost;
use App\Events\DeletePost;

class PostController extends Controller
{
    /**
     * Insert post into db and dispatch event
     * for upload post to facebook page
     */
    public function insertpost(Request $request)
    {
        $id = auth()->user()->id;
        $title = $request->title;
        $description = $request->description;
        $validator = Validator::make($request->all(), [
            'title' => 'required', 'unique:title', 'string',
            'description' => 'required',
            'image' => 'mimes:png,jpg|image|max:2048',
            'category_name' => 'required',
            'facebook_page' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try {
            $fb_page = $request->facebook_page;
            $categoryid = DB::table('categories')->where('name', $request->category_name)
                ->first('id');
            if ($request->hasFile('image')) {
                $file = $request->image;
                $extension = $request->image->extension();
                $filename = time() . '.' . $extension;
                $path = $file->storeAs('public/post/', $filename);
                $data = Post::create([
                    'userid' => $id,
                    'categoryid' => $categoryid->id,
                    'title' => $title,
                    'description' => $description,
                    'image' => $filename,
                    'created_by' => $id,
                ]);

                $user = User::where('id', $data['userid'])->first();

                if (!empty(auth()->user()->token)) {
                    event(new CreatePost($data, $user, $fb_page));
                }

                return response()->json(['success' => 'Post uploaded successfully']);
            } else {
                $data = Post::create([
                    'userid' => $id,
                    'categoryid' => $categoryid->id,
                    'title' => $title,
                    'description' => $description,
                    'created_by' => $id,
                ]);

                $user = User::where('id', $data['userid'])->first();

                if (!empty(auth()->user()->token)) {
                    event(new CreatePost($data, $user, $fb_page));
                }

                return response()->json(['success' => 'Message uploaded successfully']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Post listing
     */
    public function show()
    {
        try {
            $id = auth()->user()->id;
            $type = auth()->user()->type;
            if ($type == 1) {
                $data = POST::where('userid', $id)->get();
            } else {
                $data = POST::select('title', 'created_by')->get();
            }

            return collect($data)->transformWith(new PostListTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update post
     */
    public function updatepost(Request $request, $id)
    {
        try {
            $userid = auth()->user()->id;
            $name = auth()->user()->name;
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'mimes:png,jpg',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            $post = DB::table('posts')->where('userid', $userid)
                ->where('id', $id)
                ->first();
            if (!empty($post->image)) {
                $oldpath = public_path() . "/storage/post/$post->image";
                unlink($oldpath);
            }

            $file = $request->image;
            $extension = $request->image->extension();
            $filename = time() . '.' . $extension;
            $path = $file->storeAs('public/post/', $filename);

            DB::table('posts')->where('userid', $userid)
                ->where('id', $id)
                ->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'image' => $filename,
                    'created_by' => $name,
                ]);

            $data = DB::table('posts')->where('userid', $userid)
                ->where('id', $id)
                ->first();

            $user = User::where('id', $userid)->first();

            if (!empty(auth()->user()->token)) {
                event(new UpdatePost($data, $user));
            }

            return response()->json(['success' => 'Successfully updated!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete post
     */
    public function deletepost(Request $request, $id)
    {
        try {
            $user_id = auth()->user()->id;
            $data = DB::table('posts')->where('userid', $user_id)
                ->where('id', $id)
                ->first();

            $user = User::where('id', $user_id)->first();

            if (!empty(auth()->user()->token)) {
                event(new DeletePost($data, $user));
            }

            if (!empty($data->file)) {
                $oldpath = public_path() . "/storage/post/$data->file";
                unlink($oldpath);
                DB::table('posts')->where('userid', $user_id)
                    ->where('id', $id)
                    ->delete();
            } else {
                DB::table('posts')->where('userid', $user_id)
                    ->where('id', $id)
                    ->delete();
            }

            return response()->json(['success' => 'Deleted Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Search post
     */
    public function searchpost(Request $request)
    {
        try {
            $id = auth()->user()->id;
            $type = auth()->user()->type;
            $search = $request->search;
            if ($type == 1) {
                $data = POST::where('userid', $id)
                    ->where('title', 'LIKE', '%' . $search . '%')
                    ->get();
            } else {
                $data = POST::where('title', 'LIKE', '%' . $search . '%')->get();
            }

            return fractal($data, new PostListTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
