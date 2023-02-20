<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatePost
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $data, $user, $fb_page;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $user, $fb_page)
    {
        $this->data = $data;
        $this->user = $user;
        $this->fb_page = $fb_page;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
