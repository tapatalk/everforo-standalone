<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\Channel;

class LoginEvent extends Event implements ShouldBroadcastNow
{
    // When an event is broadcast, 
    // all of its public properties are automatically serialized and broadcast as the event's payload, 
    // allowing you to access any of its public data from your JavaScript application.
    public $token;

    public $bearer;

    // public $connection = 'redis';

    // /**
    //  * The name of the queue on which to place the event.
    //  *
    //  * @var string
    //  */
    // public $broadcastQueue = 'redis';

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    // public $broadcastQueue = 'uploadResultNotification';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($token, $bearer)
    {
        //
        $this->token = $token;
        $this->bearer = $bearer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel($this->token);
    }

}
 