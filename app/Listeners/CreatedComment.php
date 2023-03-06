<?php

namespace App\Listeners;

use App\Events\CreateComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FacebookPage;

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

            $page_token = FacebookPage::where('page_id', $page_id)
                ->pluck('access_token')
                ->first();

            $facebook_image_id = Post::where('id', $event->data['postid'])
                ->pluck('facebook_post_id')
                ->first();

            $response = Http::post(env('FACEBOOK_GRAPH_API') . $facebook_image_id . '/comments/?message=' . $event->data['comment'] . '&access_token=' . $page_token);

            $data = DB::table('comments')->where('userid', $event->data['userid'])
                ->where('id', $event->data['id'])
                ->update([
                    'facebook_post_id' => $facebook_image_id,
                    'comment_id' => $response['id'],
                    'pageid' => $page_id,
                    'created_by' => $event->user['facebook_id'],
                ]);

            Log::info($data);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}
