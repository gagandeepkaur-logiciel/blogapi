<?php

namespace App\Listeners;

use App\Events\CreatePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $data = $event->create_data[0];
        $path = env('BLOGAPI_FACEBOOK_POST'). $data['image'];
        $url = asset($path);
        $access_token = $event->create_data[1][0]['token'];
        $facebook_user_id = $event->create_data[1][0]['facebook_id'];
        
        $profile_response = Http::get(env('FACEBOOK_GRAPH_API') . 'me/accounts?access_token=' . $access_token . '');
        $page_token = $profile_response['data'][0]['access_token'];
        $page_id = $profile_response['data'][0]['id'];

        if (!empty($data['image'])) 
        {
            $photo_response = Http::attach(
                'attachment',
                file_get_contents($url),
                $data['image']
            )->post(env('FACEBOOK_GRAPH_API') . $page_id . '/photos?message=' . $data['title'] . '&access_token=' . $page_token . '');
            
            DB::table('posts')->where('userid', $data['userid'])
            ->where('id', $data['id'])
            ->update([
                'created_by' => $facebook_user_id,
                'facebook_post_id' => $photo_response['post_id'],
                'facebook_msg_id' => $photo_response['id'],
                'pageid' => $page_id,
            ]);

        }else {
            $feed_response = Http::post(env('FACEBOOK_GRAPH_API') . $page_id . '/feed?message=' . $data['title'] . '&access_token=' . $page_token . '');
            
            $facebook_data = DB::table('posts')->where('userid', $data['userid'])
            ->where('id', $data['id'])
            ->update([
                'facebook_msg_id' => $feed_response['id'],
                'created_by' => $facebook_user_id,
                'pageid' => $page_id,
            ]);
        }
    }
}
