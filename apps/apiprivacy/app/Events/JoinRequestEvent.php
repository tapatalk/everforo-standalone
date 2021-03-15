<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;
// use Illuminate\Queue\SerializesModels;

class JoinRequestEvent extends Event implements ShouldBroadcastNow
{

    // use SerializesModels;

    // Your event must use the Illuminate\Broadcasting\InteractsWithSockets trait 
    // in order to call the toOthers method.
    // use Illuminate\Broadcasting\InteractsWithSockets;

    public $group_admins;

    public $payload;

    // public $connection = 'redis';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($group_admins, $payload)
    {
        //
        $this->group_admins = $group_admins;
        $this->payload = $payload;
    }

    /**
     * return a channel or array of channels that the event should broadcast on. 
     * The channels should be instances of Channel, PrivateChannel, or PresenceChannel.
     *
     * @return array
     */
    public function broadcastOn()
    {
        $channels = [];
        foreach ($this->group_admins as $recipient_id) {
            $channels[] = new PrivateChannel('web-user.' . $recipient_id);
        }

        return $channels;
    }

    /**
     * normally all the public properties get broadcasted,
     * define a broadcastWith method to return a fine-grained broadcast payload 
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->payload;
    }
}
 