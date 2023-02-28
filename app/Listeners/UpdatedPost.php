<?php

namespace App\Listeners;

use App\Events\UpdatePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatedPost implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */

    public $tries = 2;

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UpdatePost  $event
     * @return void
     */
    public function handle(UpdatePost $event)
    {
        try {
            $data = $event->data;
            $user = $event->user;
            $path = 'http://localhost/ojt/blogapi/storage/app/public/post/' . $data->image;

            $access_token = $event->user['token'];
            $facebook_user_id = $event->user['facebook_id'];

            $profile_response = Http::get('https://graph.facebook.com/v16.0/me/accounts?access_token=' . $access_token);

            $count = count($profile_response['data']);

            foreach ($profile_response['data'] as $count) {
                if ($count['id'] == $data->pageid) {
                    $page_token = $count['access_token'];

                    $response =  Http::get('https://graph.facebook.com/v16.0/' . $data->pageid . '/feed?access_token=' . $page_token);

                    $post_count = count($response['data']);
                    $post = $response['data'];

                    foreach ($post as $post_count) {
                        if ($post_count['id'] == $data->facebook_post_id) {
                            $post_id = $post_count['id'];
                        }
                    }

                    $feed_response = Http::post('https://graph.facebook.com/v16.0/' . $post_id . '?message=' . $data->title . '&access_token=' . $page_token);

                    Log::info($feed_response);
                }
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
