<?php

namespace App\Listeners;

use App\Events\DeletePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\{
    Http,
    Log
};
use App\Models\FacebookPage;
use App\Http\Controllers\FacebookController;

class DeletedPost implements ShouldQueue
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
     * @param  \App\Events\DeletePost  $event
     * @return void
     */
    public function handle(DeletePost $event)
    {
        try {

            if (!empty($event->user['token']))
                $response = Http::delete(env('FACEBOOK_GRAPH_API') . $event->data->facebook_post_id . '?access_token=' . page_token($event->data->pageid));

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
