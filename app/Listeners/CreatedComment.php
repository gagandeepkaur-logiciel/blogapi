<?php

namespace App\Listeners;

use App\Events\CreateComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreatedComment implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 2;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CreateComment  $event
     * @return void
     */
    public function handle(CreateComment $event)
    {
        try {
            $page_id = Post::where('id', $event->data['postid'])
                ->pluck('pageid')
                ->first();

            $facebook_image_id = Post::where('id', $event->data['postid'])
                ->pluck('facebook_post_id')
                ->first();

            $profile_response = Http::get(env('FACEBOOK_GRAPH_API') .'me/accounts?access_token=' . $event->user['token']);

            $count = count($profile_response['data']);
            $pr = $profile_response['data'];
            foreach ($pr as $count) {
                if ($count['id'] == $page_id) {
                    $page_token = $count['access_token'];
                }
            }

            $feed_response = Http::get(env('FACEBOOK_GRAPH_API') . $page_id . '/feed?&access_token=' . $page_token);

            $comment_response = Http::post(env('FACEBOOK_GRAPH_API') . $facebook_image_id . '/comments/?message=' . $event->data['comment'] . '&access_token=' . $page_token . '');

            $data = DB::table('comments')->where('userid', $event->data['userid'])
                ->where('id', $event->data['id'])
                ->update([
                    'facebook_post_id' => $facebook_image_id,
                    'comment_id' => $comment_response['id'],
                    'pageid' => $page_id,
                    'created_by' => $event->user['facebook_id'],
                ]);

            Log::info($data);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}
