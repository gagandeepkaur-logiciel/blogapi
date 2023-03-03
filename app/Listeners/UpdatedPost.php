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
            $access_token = $event->user['token'];
            $facebook_user_id = $event->user['facebook_id'];

            $profile_response = Http::get(env('FACEBOOK_GRAPH_API') . 'me/accounts?access_token=' . $access_token);

            $count = count($profile_response['data']);

            foreach ($profile_response['data'] as $count) {
                if ($count['id'] == $event->data->pageid) {
                    $page_token = $count['access_token'];

                    $response =  Http::get(env('FACEBOOK_GRAPH_API') . $event->data->pageid . '/feed?access_token=' . $page_token);

                    $post_count = count($response['data']);
                    $post = $response['data'];

                    foreach ($post as $post_count) {
                        if ($post_count['id'] == $event->data->facebook_post_id) {
                            $post_id = $post_count['id'];
                        }
                    }

                    $response = Http::post(env('FACEBOOK_GRAPH_API') . $post_id . '?message=' . $event->data->title . '&access_token=' . $page_token);

                    Log::info($response);
                }
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
