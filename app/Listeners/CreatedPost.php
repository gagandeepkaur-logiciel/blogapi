<?php

namespace App\Listeners;

use App\Events\CreatePost;
use App\Http\Controllers\FacebookController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Http,
};
use App\Models\{
    FacebookPage,
    User
};

class CreatedPost implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    /**
     * Create the event listener.   
     *
     * @return void
     */
    // private $fb_page;

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
        try {
            $data = check_Page_Token($event->fb_page);
            if (!empty($event->user['token'])) {

                if (!empty($event->data['image'])) {
                    $path = env('BLOGAPI_FACEBOOK_POST') . $event->data['image'];
                    $url = asset($path);

                    $response = Http::attach(
                        'attachment',
                        file_get_contents($url),
                        $event->data['image']
                    )->post(env('FACEBOOK_GRAPH_API') . $data['page_id'] . '/photos?message=' . $event->data['title'] . '&access_token=' . $data['access_token']);
                } else {
                    $response = Http::post(env('FACEBOOK_GRAPH_API') . $data['page_id'] . '/feed?message=' . $event->data['title'] . '&access_token=' . $data['access_token']);
                }

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
        $data = check_Page_Token($event->fb_page);
        $updated_data = DB::table('posts')->where('userid', $event->data['userid'])
            ->where('id', $event->data['id'])
            ->update([
                'facebook_post_id' => $response['post_id'],
                'pageid' => $data['page_id'],
                'created_by' => $event->user['facebook_id'],
            ]);

        Log::info($updated_data);
    }
}
