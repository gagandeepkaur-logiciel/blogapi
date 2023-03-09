<?php

namespace App\Listeners;

use App\Events\CreateComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\{
    DB,
    Http,
    Log
};
use App\Models\{
    FacebookPage,
    Post
};
use App\Http\Controllers\FacebookController;

class CreatedComment implements ShouldQueue
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
     * @param  \App\Events\CreateComment  $event
     * @return void
     */
    public function handle(CreateComment $event)
    {
        try {
            if (!empty($event->user['token'])) {
                $data = check_tokens($event->data['postid']);

                $response = Http::post(env('FACEBOOK_GRAPH_API') . $data['facebook_post_id'] . '/comments/?message=' . $event->data['comment'] . '&access_token=' . page_token($data['pageid']));

                if ($response->failed()) {
                    $this->check_response($response, $event);
                } else {
                    $this->update_record($response, $event);
                }
            }
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
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
    
    private function update_record($response, $event)
    {
        $data = check_tokens($event->data['postid']);
        $updated_data = DB::table('comments')->where('userid', $event->data['userid'])
            ->where('id', $event->data['id'])
            ->update([
                'facebook_post_id' => $data['facebook_post_id'],
                'comment_id' => $response['id'],
                'pageid' => $data['pageid'],
                'created_by' => $event->user['facebook_id'],
            ]);

        Log::info($updated_data);
    }
}
