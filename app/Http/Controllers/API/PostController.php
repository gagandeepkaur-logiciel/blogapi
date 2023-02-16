<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Transformers\PostListTransformer;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
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
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        try {
            $categoryid = DB::table('categories')->where('name', $request->category_name)->first('id');
            if (!empty(auth()->user()->token)) {
                $accesstoken = auth()->user()->token;
                $facebook_user_id = auth()->user()->facebook_id;
                $res = Http::get('https://graph.facebook.com/v15.0/me/accounts?access_token=' . $accesstoken . '');
                $pagetoken = $res['data'][0]['access_token'];
                $pageid = $res['data'][0]['id'];
                if ($request->hasFile('image')) {
                    $file = $request->image;
                    $extension = $request->image->extension();
                    $filename = time() . '.' . $extension;
                    $file->storeAs('public/post/', $filename);
                    $success = Post::create([
                        'userid' => $id,
                        'categoryid' => $categoryid->id,
                        'title' => $title,
                        'description' => $description,
                        'image' => $filename,
                        'created_by' => $facebook_user_id,
                    ]);
                    $response = Http::attach(
                        'attachment',
                        file_get_contents($request->image),
                        $filename
                    )->post('https://graph.facebook.com/v15.0/' . $pageid . '/photos?message=' . $title . '&access_token=' . $pagetoken . '');
                    $facebook_data = DB::table('posts')->where('userid', $id)->where('id', $success['id'])->update([
                        'facebook_post_id' => $response['post_id'],
                        'facebook_msg_id' => $response['id'],
                        'pageid' => $pageid,
                    ]);
                    return $response->json();
                } else {
                    $success = Post::create([
                        'userid' => $id,
                        'categoryid' => $categoryid->id,
                        'title' => $title,
                        'description' => $description,
                        'created_by' => $facebook_user_id,
                    ]);
                    $response = Http::post('https://graph.facebook.com/v15.0/' . $pageid . '/feed?message=' . $title . '&access_token=' . $pagetoken . '');
                    $facebook_data = DB::table('posts')->where('id', $id)->where('id', $success['id'])->update([
                        'facebook_msg_id' => $response['id'],
                        'pageid' => $pageid,
                    ]);
                    return response()->json(['success' => $facebook_data]);
                }
            } else {
                if ($request->hasFile('image')) {
                    $file = $request->image;
                    $extension = $request->image->extension();
                    $filename = time() . '.' . $extension;
                    $file->storeAs('public/post/', $filename);
                    $success = Post::create([
                        'userid' => $id,
                        'categoryid' => $categoryid->id,
                        'title' => $title,
                        'description' => $description,
                        'image' => $filename,
                        'created_by' => $id,
                    ]);
                    return response()->json(['success' => $success]);
                } else {
                    $success = Post::create([
                        'userid' => $id,
                        'categoryid' => $categoryid->id,
                        'title' => $title,
                        'description' => $description,
                        'created_by' => $id,
                    ]);
                    return response()->json(['success' => $success]);
                }
            }
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

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
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updatepost(Request $request, $id)
    {
        try {
            $userid = auth()->user()->id;
            $name = auth()->user()->name;
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'file' => 'mimes:png,jpg',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }
            $data = DB::table('posts')->where('userid', $userid)->where('id', $id)->first();
            if (!empty($data->file)) {
                $oldpath = public_path() . "/storage/post/$data->file";
                unlink($oldpath);
            }
            $file = $request->file;
            $extension = $request->file->extension();
            $filename = time() . '.' . $extension;
            $path = $file->storeAs('public/post/', $filename);
            $data = DB::table('posts')->where('userid', $userid)->where('id', $id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filename,
                'created_by' => $name,
            ]);

            return response()->json(['success' => 'Successfully updated!']);
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deletepost(Request $request)
    {
        try {
            $id = auth()->user()->id;
            $data = DB::table('posts')->where('userid', $id)->where('title', $request->title)->first();
            if (!empty($data->file)) {
                $oldpath = public_path() . "/storage/post/$data->file";
                unlink($oldpath);
                DB::table('posts')->where('userid', $id)->where('title', $request->title)->delete();
            }
            return response()->json(['success' => 'Deleted Successfully!']);
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function searchpost(Request $request)
    {
        try {
            $id = auth()->user()->id;
            $type = auth()->user()->type;
            $search = $request->search;
            if ($type == 1) {
                $data = POST::where('userid', $id)->where('title', 'LIKE', '%' . $search . '%')->get();
            } else {
                $data = POST::where('title', 'LIKE', '%' . $search . '%')->get();
            }
            return fractal($data, new PostListTransformer())->toArray();
        } catch (\Exception$e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
