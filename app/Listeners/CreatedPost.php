<?php

namespace App\Listeners;

use App\Events\CreatePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatedPost implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    public $backoff = 1;
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
     * @param  \App\Events\CreatePost  $event
     * @return void
     */
    public function handle(CreatePost $event)
    {
        try {
            $post = $event->data;
            $user = $event->user;
            $fb_page = $event->fb_page;
            $path = 'http://localhost/ojt/blogapi/storage/app/public/post/' . $post['image'];
            // $url = asset($path);
            $access_token = $event->user['token'];
            $facebook_user_id = $event->user['facebook_id'];

            $profile_response = Http::get('https://graph.facebook.com/v16.0/me/accounts?access_token='.$access_token);
            
            $count = count($profile_response['data']);
            $pr = $profile_response['data'];
            
            foreach ($pr as $count) {
                if ($count['name'] == $fb_page) {
                    $page_token = $count['access_token'];
                    $page_id = $count['id'];
                    
                    if (!empty($post['image'])) {
                        $photo_response = Http::attach(
                            'attachment',
                            file_get_contents($path),
                            $post['image']
                            )->post('https://graph.facebook.com/v16.0/' . $page_id . '/photos?message=' . $post['title'] . '&access_token=' . $page_token . '');
                            
                        $data = DB::table('posts')->where('userid', $post['userid'])
                            ->where('id', $post['id'])
                            ->update([
                                'facebook_post_id' => $photo_response['post_id'],
                                'pageid' => $page_id,
                                'created_by' => $facebook_user_id,
                            ]);
                            Log::info($data);
                    } else {
                        $feed_response = Http::post('https://graph.facebook.com/v16.0/'. $page_id . '/feed?message=' . $post['title'] . '&access_token=' . $page_token);

                        $data = DB::table('posts')->where('userid', $post['userid'])
                            ->where('id', $post['id'])
                            ->update([
                                'facebook_post_id' => $feed_response['id'],
                                'pageid' => $page_id,
                                'created_by' => $facebook_user_id,
                            ]);
                            Log::info($data);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::critical($e->getMessage());
        }
    }
}
