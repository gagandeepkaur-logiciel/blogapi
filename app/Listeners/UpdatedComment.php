<?php

namespace App\Listeners;

use App\Events\UpdateComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Comment;
use Illuminate\Support\Facades\{
    Http,
    Log
};
use App\Http\Controllers\FacebookController;

class UpdatedComment implements ShouldQueue
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
     * @param  \App\Events\UpdateComment  $event
     * @return void
     */
    public function handle(UpdateComment $event)
    {
        try {
            $response = Http::post(env('FACEBOOK_GRAPH_API') . $event->data['comment_id'] . '?message=' . $event->data['comment'] . '&access_token=' . page_token($event->data['pageid']));

            if ($response->failed())
                $this->check_response($response, $event);
            else
                Log::info($response);
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }

    private function check_response($response, $event)
    {
        if ($response['error']['code'] == 190)
            $var = new FacebookController;
        $data = $var->update_tokens_from_facebook($event->data['userid']);
        $this->handle($event);
    }
}
