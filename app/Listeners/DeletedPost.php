<?php

namespace App\Listeners;

use App\Events\DeletePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeletedPost implements ShouldQueue
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
     * @param  \App\Events\DeletePost  $event
     * @return void
     */
    public function handle(DeletePost $event)
    {
        try {
            $profile_response = Http::get(env('FACEBOOK_GRAPH_API') . 'me/accounts?access_token=' . $event->user['token']);

            $count = count($profile_response['data']);

            foreach ($profile_response['data'] as $count) {
                if ($count['id'] == $event->data->pageid) {
                    $page_token = $count['access_token'];
                }
            }

            $feed_response = Http::get(env('FACEBOOK_GRAPH_API') . $event->data->pageid . '/feed?&access_token=' . $page_token);

            $post_count = count($feed_response['data']);

            foreach ($feed_response['data'] as $post_count) {
                if ($post_count['id'] == $event->data->facebook_post_id) {
                    $post_id = $post_count['id'];
                }
            }

            $post_response = Http::delete(env('FACEBOOK_GRAPH_API') . $post_id . '?access_token=' . $page_token);

            Log::info($post_response);
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
