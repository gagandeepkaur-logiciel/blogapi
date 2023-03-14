<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Storage;

class WebhookController extends Controller
{
    /**
     * Redirect from webhook
     */
    public function backFromWebHook(Request $request)
    {
        try {
            $data = Post::where('facebook_post_id', $request->id)->first();
            return $request;
            if (empty($data)) {
                Log::info([$request]);
                $split_page_id  = preg_split('/[_]/', $request['id']);
                $page_id = current($split_page_id);
                if (!empty($request['full_picture'])) {
                    $contents = file_get_contents($request['full_picture']);

                    $components = preg_split('/[?]/', $request['full_picture']);
                    $current = current($components);
                    $end = preg_split('/[.]/', $current);
                    $ext = end($end);
                    $filename = time() . '.' . $ext;
                    $new = public_path('storage/post/') . $filename;
                    file_put_contents($new, $contents);

                    $data = Post::create([
                        'userid' => auth()->user()->id,
                        'categoryid' => 6,
                        'title' => $request->message,
                        'description' => 'description',
                        'image' => $filename,
                        'facebook_post_id' => $request->id,
                        'pageid' => $page_id,
                        'created_by' => auth()->user()->facebook_id,
                        'created_at' => $request['created_time'],
                        'updated_at' => $request['updated_time'],
                    ]);

                    Log::info($data);
                } else {
                    $data = Post::create([
                        'userid' => auth()->user()->id,
                        'categoryid' => 6,
                        'title' => $request->message,
                        'description' => 'description',
                        'facebook_post_id' => $request->id,
                        'pageid' => $page_id,
                        'created_by' => auth()->user()->facebook_id,
                        'created_at' => $request['created_time'],
                        'updated_at' => $request['updated_time'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
