<?php

namespace App\Listeners;

use App\Events\CreatePost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreatedPost implements ShouldQueue
{
    use InteractsWithQueue;
     /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CreatePost  $event
     * @return void
     */
    public function handle(CreatePost $event)
    {
        $data = $event->create_data[1];
        $accesstoken = auth()->user()->token;
                $facebook_user_id = auth()->user()->facebook_id;
                $res = Http::get(env('FACEBOOK_GRAPH_API') . 'me/accounts?access_token=' . $accesstoken . '');
                $pagetoken = $res['data'][0]['access_token'];
                $pageid = $res['data'][0]['id'];
                if (!empty($data['image'])) {
                        $photoresponse = Http::attach(
                        'attachment',
                        file_get_contents($event->create_data[0]['image']),
                        $data['image']
                    )->post(env('FACEBOOK_GRAPH_API') . $pageid . '/photos?message=' . $data['title'] . '&access_token=' . $pagetoken . '');
                    DB::table('posts')->where('userid', $data['userid'])->where('id', $data['id'])->update([
                        'created_by' => $facebook_user_id,
                        'facebook_post_id' => $photoresponse['post_id'],
                        'facebook_msg_id' => $photoresponse['id'],
                        'pageid' => $pageid,
                    ]);
                }else{
                    $feedresponse = Http::post(env('FACEBOOK_GRAPH_API') . $pageid . '/feed?message=' . $data['title'] . '&access_token=' . $pagetoken . '');
                    $facebook_data = DB::table('posts')->where('userid', $data['userid'])->where('id', $data['id'])->update([
                        'facebook_msg_id' => $feedresponse['id'],
                        'pageid' => $pageid,
                    ]);
                }
            }
}
