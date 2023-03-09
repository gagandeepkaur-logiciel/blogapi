<?php

namespace App\Listeners;

use App\Events\UpdatePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Http
};
use App\Http\Controllers\FacebookController;

class UpdatedPost implements ShouldQueue
{
    use InteractsWithQueue;
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
     * @param  \App\Events\UpdatePost  $event
     * @return void
     */
    public function handle(UpdatePost $event)
    {
        try {
            if (!empty($event->user['token'])) {
                $response = Http::post(env('FACEBOOK_GRAPH_API') . $event->data->facebook_post_id . '?message=' . $event->data->title . '&access_token=' . page_token($event->data->pageid));
            }

            if ($response->failed()) {
                $this->check_response($response, $event);
            } else {
                Log::info($response);
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }

    private function check_response($response, $event)
    {
        if ($response['error']['code'] == 190) {
            $var = new FacebookController;
            $data = $var->update_tokens_from_facebook($event->data['userid']);
            $this->handle($event);
        }
    }
}
