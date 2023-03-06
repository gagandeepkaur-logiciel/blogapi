<?php

namespace App\Listeners;

use App\Events\CreatePost;
use App\Models\FacebookPage;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatedPost implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */

    public $tries = 2;

    /**
     * Create the event listener.   
     *
     * @return void
     */

    // public function tokens($user)
    // {

    //     $token = $user['token'];
    // }

    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  \App\Events\CreatePost  $event
     * @return void
     */
    public function handle(CreatePost $event)
    {
        try {
            $page_id = FacebookPage::where('page_name', $event->fb_page)
                ->pluck('page_id')
                ->first();
            $page_token = FacebookPage::where('page_id', $page_id)
                ->pluck('access_token')
                ->first();

            if (!empty($event->user['token'])) {
                $path = env('BLOGAPI_FACEBOOK_POST') . $event->data['image'];
                $url = asset($path);

                if (!empty($event->data['image'])) {
                    $photo_response = Http::attach(
                        'attachment',
                        file_get_contents($url),
                        $event->data['image']
                    )->post(env('FACEBOOK_GRAPH_API') . $page_id . '/photos?message=' . $event->data['title'] . '&access_token=' . $page_token);
                } else {
                    $feed_response = Http::post(env('FACEBOOK_GRAPH_API') . $page_id . '/feed?message=' . $event->data['title'] . '&access_token=' . $page_token);
                }
            }

            $data = DB::table('posts')->where('userid', $event->data['userid'])
                ->where('id', $event->data['id'])
                ->update([
                    'facebook_post_id' => $photo_response['post_id'],
                    'pageid' => $page_id,
                    'created_by' => $event->user['facebook_id'],
                ]);

            Log::info($data);
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}
