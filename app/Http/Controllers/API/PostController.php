<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Transformers\PostListTransformer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Validator,
    Storage,
};
use App\Models\{
    User,
    Post,
    Folder,
};
use App\Events\{
    UpdatePost,
    DeletePost,
    CreatePost,
};

class PostController extends Controller
{
    /**
     * Insert post into db and dispatch event
     * for upload post to facebook page
     */
    public function insertpost(Request $request)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'title' => 'required', 'unique:title', 'string',
                'description' => 'required',
                'image' => 'mimes:png,jpg|image|max:2048',
                'category_id' => 'required',
                'facebook_page' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            if ($request->hasFile('image')) {
                $dir_name = Folder::where('id', $input['folder_id'])->first();

                $filename = time() . '.' . $input['image']->extension();
                if (!empty($dir_name)) {
                    $input['image']->storeAs(directory_path($dir_name['name']) . '/', $filename);
                } else {
                    $input['image']->storeAs('/public/directoryManager/post/', $filename);
                }

                $data = Post::create([
                    'userid' => auth()->user()->id,
                    'categoryid' => $input['category_id'],
                    'folder_id' => empty($input['folder_id']) ? '18' : $input['folder_id'],
                    'title' => $input['title'],
                    'description' => $input['description'],
                    'image' => $filename,
                    'created_by' => auth()->user()->id,
                ]);


                if (!empty(auth()->user()->token)) {
                    $fb_page = $input['facebook_page'];
                    $user = User::where('id', $data['userid'])->first();

                    event(new CreatePost($data, $user, $fb_page));
                }

                return response()->json(['success' => 'Post uploaded successfully']);
            } else {
                $data = Post::create([
                    'userid' => auth()->user()->id,
                    'categoryid' => $input['category_id'],
                    'title' => $input['title'],
                    'description' => $input['description'],
                    'created_by' => auth()->user()->id,
                ]);

                $user = User::where('id', $data['userid'])->first();

                if (!empty(auth()->user()->token)) {
                    $fb_page = $input['facebook_page'];
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
            if ($type == 1)
                $data = POST::where('userid', $id)->get();
            else
                $data = POST::select('title', 'created_by')->get();

            return  fractal($data, new PostListTransformer())->toArray();
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
            $input = $request->all();
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'mimes:png,jpg',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            $before_update = DB::table('posts')->where('userid', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!empty($input['image'])) {
                $folder = Folder::where('id', $before_update->folder_id)->first();

                if (!empty($before_update->image)) {
                    $oldpath = directory_path($folder['name']) . '/' . $before_update->image;
                    Storage::delete($oldpath);
                }

                $filename = time() . '.' . $input['image']->extension();
                $input['image']->storeAs(directory_path($folder['name']) . '/', $filename);
            }

            DB::table('posts')->where('userid', auth()->user()->id)
                ->where('id', $id)
                ->update([
                    'title' => $input['title'],
                    'description' => $input['description'],
                    'image' => empty($input['image']) ? $before_update->image : $filename,
                    'created_by' => auth()->user()->id,
                ]);

            $data = DB::table('posts')->where('userid', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!empty(auth()->user()->token)) {
                $user = User::where('id', auth()->user()->id)->first();
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
            $data = DB::table('posts')->where('userid', auth()->user()->id)
                ->where('id', $id)
                ->first();

            if (!empty(auth()->user()->token)) {
                $user = User::where('id', auth()->user()->id)->first();
                event(new DeletePost($data, $user));
            }

            if (!empty($data->image)) {
                $folder = Folder::where('id', $data->folder_id)->first();

                Storage::delete(directory_path($folder['name']) . '/' . $data->image);

                DB::table('posts')->where('userid', auth()->user()->id)
                    ->where('id', $id)
                    ->delete();
            } else {
                DB::table('posts')->where('userid', auth()->user()->id)
                    ->where('id', $id)
                    ->delete();
            }

            return response()->json(['success' => 'Deleted Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Search post
     */
    public function searchpost(Request $request)
    {
        try {
            if (auth()->user()->type == 1) {
                $data = POST::where('userid', auth()->user()->id)
                    ->where('title', 'LIKE', '%' . $request->search . '%')
                    ->get();
            } else {
                $data = POST::where('title', 'LIKE', '%' . $request->search . '%')->get();
            }

            return fractal($data, new PostListTransformer())->toArray();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
