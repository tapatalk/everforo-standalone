<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Thread;
use App\Models\Post;
use App\User;

class AirdropActivitiesEvent extends Event implements ShouldBroadcastNow
{
    // Your event must use the Illuminate\Broadcasting\InteractsWithSockets trait 
    // in order to call the toOthers method.
    // use Illuminate\Broadcasting\InteractsWithSockets;

    public $_payload;

    // public $connection = 'redis';

    /**
     * The name of the queue on which to place the event.
     * TODO we are using sync for now. we should config it to a real asyc queue
     *
     * @var string
     */
    // public $broadcastQueue = 'sync | beanstalkd | sqs | redis';

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->_payload = $payload;
    }

    /**
     * return a channel or array of channels that the event should broadcast on. 
     * The channels should be instances of Channel, PrivateChannel, or PresenceChannel.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('web-user.' . $this->_payload['user_id']);
    }

    /**
     * normally all the public properties get broadcasted,
     * define a broadcastWith method to return a fine-grained broadcast payload 
     *
     * @return array
     */
    public function broadcastWith()
    {
        \Log::error(json_encode( $this->_payload));
        return [
            'type' => 'airdrop',
            'thread_id' => 0,
            'post_id' => 0,
            'user' => [
                'photo_url' => $this->_payload['logo'],
                'name' => '',
            ],
            'post_content' => '',
            'post_parent_id' => 0,
            'created_at' => date("Y-m-d H:i:s"),
            'msg' => $this->_payload['msg'],
        ];
    }
}
 