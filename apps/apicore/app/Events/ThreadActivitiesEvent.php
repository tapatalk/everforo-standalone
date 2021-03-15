<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;
// use Illuminate\Queue\SerializesModels;

class ThreadActivitiesEvent extends Event implements ShouldBroadcastNow
{

    // use SerializesModels;

    // Your event must use the Illuminate\Broadcasting\InteractsWithSockets trait 
    // in order to call the toOthers method.
    // use Illuminate\Broadcasting\InteractsWithSockets;

    public $subscribers;

    public $payload;

    // public $connection = 'redis';

    /**
     * The name of the queue on which to place the event.
     * TODO we are using sync for now. we should config it to a real asyc queue
     *
     * @var string
     */
    // public $broadcastQueue = 'sync | beanstalkd | sqs | redis';

    // public $broadcastQueue = 'redis';

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
    public function __construct($subscribers, $payload)
    {
        //
        $this->subscribers = $subscribers;
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
        foreach ($this->subscribers as $recipient_id) {
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
 