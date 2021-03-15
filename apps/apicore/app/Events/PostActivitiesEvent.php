<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Thread;
use App\Models\Post;
use App\User;

class PostActivitiesEvent extends Event implements ShouldBroadcastNow
{
    // Your event must use the Illuminate\Broadcasting\InteractsWithSockets trait 
    // in order to call the toOthers method.
    // use Illuminate\Broadcasting\InteractsWithSockets;

    public $thread;

    public $post;

    public $user;

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
    public function __construct(Thread $thread, Post $post, User $user)
    {
        $this->thread = $thread;
        $this->post = $post;
        $this->user = $user;
    }

    /**
     * return a channel or array of channels that the event should broadcast on. 
     * The channels should be instances of Channel, PrivateChannel, or PresenceChannel.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('web-user.' . $this->post->user_id);
    }

    /**
     * normally all the public properties get broadcasted,
     * define a broadcastWith method to return a fine-grained broadcast payload 
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'type' => 'like',
            'thread_id' => $this->post->thread_id,
            'post_id' => $this->post->post_id,
            'user' => [
                'photo_url' => $this->user->photo_url,
                'name' => $this->user->name,
            ],
            'post_content' => $this->post->content,
            'post_parent_id' => $this->post->parent_id,
            'created_at' => date("Y-m-d H:i:s"),
            'msg' => \App\Utils\StringHelper::getNotificationTitle('like', $this->user->name, $this->thread->title),
        ];
    }
}
 