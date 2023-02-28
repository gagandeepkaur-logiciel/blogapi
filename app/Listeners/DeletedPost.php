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
            $data = $event->data;
        $user = $event->user;

        $profile_response = Http::get('https://graph.facebook.com/v16.0/me/accounts?access_token=' . $user['token']);

        $count = count($profile_response['data']);

        foreach ($profile_response['data'] as $count) {
            if ($count['id'] == $data->pageid) {
                $page_token = $count['access_token'];
            }
        }

        $feed_response = Http::get('https://graph.facebook.com/v16.0/' . $data->pageid . '/feed?&access_token=' . $page_token);

        $post_count = count($feed_response['data']);

        foreach ($feed_response['data'] as $post_count) {
            if ($post_count['id'] == $data->facebook_post_id) {
                $post_id = $post_count['id'];
            }
        }

        $post_response = Http::delete('https://graph.facebook.com/v16.0/'.$post_id.'?access_token=' . $page_token);

        Log::info($post_response);

        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
