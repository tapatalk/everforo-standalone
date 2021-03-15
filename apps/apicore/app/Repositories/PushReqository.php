<?php

namespace App\Repositories;

use App\Models\Notifications;
use App\Models\Subscriptions;
use App\Jobs\ReplyPushJob;
use App\User;

use Log, Queue;
class PushRepository {



    public static function sendPushJobs($group_id, $user_list, 
                                                    \App\Models\Thread $thread,
                                                    \App\Models\Post $post,
                                                   User $user,
                                                   $type)
    {
        $notifications = [];

        $unread_notifications = Notifications::select('recipient_id')
                                ->where('group_id', $group_id)
                                ->where('thread_id', $thread->id)
                                ->where('type', 'reply')
                                ->where('read', '0')
                                ->get()->toArray();

        if ($unread_notifications) {
            $unread_notifications = array_column($unread_notifications, 'recipient_id');
        }

        $recipients = array();

        foreach ($user_list as $recipient_id) {
            // if this user already have an unread notifications of this thread, don't send again
            if (!empty($unread_notifications) && in_array($recipient_id, $unread_notifications)) {
             //   continue;
            }
            if($recipient_id != $user->id){
                $recipients[] = $recipient_id;
            }
        }

        $raw_content_array = json_decode($post->content);
  
        if(is_array($raw_content_array) and isset($raw_content_array[0]->insert)){
            $content = $raw_content_array[0]->insert;

        }  else {
            $content = $post->content;
        }
        if(sizeof($recipients) > 0){
            $payload = array();
            $payload['group_id']  = $group_id;
            $payload['thread_id'] = $thread->id;
            $payload['post_id']   = $post->id;
            $payload['recipients'] = $recipients;
            $payload['username'] = $user->name;
             $payload['user_id'] = $user->id;
            $payload['thread_title'] = $thread->title;
            $payload['thread_content'] = $content;
            $payload['push_type'] = $type;
            if (env('APP_ENV') !== 'local'){
                Queue::push(new ReplyPushJob($payload));
            }
        }
        
        return $notifications;
    }

    public static function addLikeNotifications(\App\Models\Post $post, \App\Models\User $user)
    {
        Notifications::create([
            'recipient_id' => $post->user_id,
            'user_id' => $user->id,
            'group_id' => $post->group_id,
            'thread_id' => $post->thread_id,
            'post_id' => $post->id,
            'type' => 'like',
            'created_at' => date("Y-m-d H:i:s"),
        ]);
    }

}