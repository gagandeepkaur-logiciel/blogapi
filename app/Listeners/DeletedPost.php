<?php

namespace App\Listeners;

use App\Events\DeletePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FacebookPage;

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

            if (!empty(auth()->user()->token)) {
                $page_token = FacebookPage::where('page_id', $event->data->pageid)
                    ->pluck('access_token')
                    ->first();

                $response = Http::delete(env('FACEBOOK_GRAPH_API') . $event->data->facebook_post_id . '?access_token=' . $page_token);

                Log::info($response);
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }
    }
}
