<?php

namespace App\Repositories;

use App\Models\Notifications;
use App\Models\Subscriptions;
use App\Models\Thread;
use App\Models\Group;
use App\Models\GroupErc20Token;
use App\User;
use App\Models\Report;
use App\Utils\StringHelper;

class NotificationsRepository {

    /**
     * add thread subscriber
     */
    public static function subscribe($user_id, $group_id, $thread_id){
        Subscriptions::firstOrCreate([
            'user_id' => $user_id,
            'group_id' => $group_id,
            'thread_id' => $thread_id,
        ]);
    }

    /**
     * get thread subscribers
     */
    public static function getThreadSubscribers($group_id, $thread_id, $except_user_id) {
        // no notification to unfollowed group
        $subscribers = \DB::table('subscriptions')
                        ->select('subscriptions.user_id')
                        ->join('group_follow', 'subscriptions.user_id', '=', 'group_follow.user_id')
                        ->where('group_follow.group_id', $group_id)
                        ->whereNull('group_follow.deleted_at')
                        ->where('subscriptions.thread_id', $thread_id)
                        ->where('subscriptions.user_id', '<>', $except_user_id)
                        ->get()->toArray();
        if ($subscribers){
            return array_column($subscribers, 'user_id');
        }
        return [];
    }

    public static function getNotifications($user_id, $offset, $page_length){
         $notifications = Notifications::select('id','user_id','group_id','thread_id','post_id','type','read', 'msg', 'created_at', 'recipient_id')
                            ->where('recipient_id', $user_id)
                            // ->where('read', 0)
                            ->offset($offset)->limit($page_length)
                            ->orderBy('id', 'desc')->get();
         $banUser = new BanUsersRepository();
         foreach($notifications as $notification){
            $thread = Thread::find($notification->thread_id);

            if (isset($thread->title)){
                $notification->title = $thread->title;
                $notification->thread_slug = StringHelper::slugify($thread->title,$thread->id);
            } else {
                $notification->thread_slug = $notification->thread_id;
            }

            $user_id = $notification->user_id ? $notification->user_id : $notification->recipient_id;
            //add user ban status
            $notification->is_ban = $banUser->checkUserBan($user_id, $notification->group_id);

            if (UserBehaviorRepository::checkUserOnline($notification->user_id)) {
                $notification->online = true;
            } else {
                $notification->online = false;
            }

            $user = User::select('photo_url','name','id')->find($user_id);
            $group = Group::withTrashed()->find($notification->group_id);
            $notification->user = $user;
            $notification->group_name = isset($group->name) ? $group->name : '';
            $flag_reason = "";

            if ($notification->type == 'flag') {
                $report = Report::where('post_id', $notification->post_id)
                        ->where('user_id', $notification->user_id)
                        ->first();

                $flag_reason = $report->reason;
            } elseif ($notification->type == 'airdrop') {

                $token = GroupErc20Token::with('erc20_token')->where('group_id', $group->id)->first();

                if ($token && $token->erc20_token) {
                    $notification->token = new \stdClass();
                    $notification->token->name = $token->erc20_token->name;
                    $notification->token->logo = $token->erc20_token->logo;
                }
            }

            $msg = StringHelper::getNotificationTitle($notification->type, isset($user->name) ? $user->name : '', isset($thread->title) ? $thread->title : '', $flag_reason);
    
            if ($msg && $notification->type != 'join' && $notification->type != 'join_request') {
                $notification->msg =$msg;
            }

            $url = env('EVERFORO_DOMAIN')."/g/".$notification->group_name."/thread/".$notification->thread_id;
            $notification->url = $url;

            
         }
         return $notifications;

    }

    /**
     * 
     */
    public static function readNotifications($recipient_id, $notification_ids) 
    {
        return Notifications::where('recipient_id', $recipient_id)
                    ->whereIn('id', $notification_ids)
                    ->update(['read' => 1]);
    }

    public static function readNotificationsByGroup($recipient_id, $group_id) 
    {
        return Notifications::where('recipient_id', $recipient_id)
                    ->where('group_id', $group_id)
                    ->update(['read' => 1]);
    }

    public static function addNewReplyNotifications($group_id, $thread_id, $user_list, 
                                                    \App\Models\Thread $thread,
                                                    \App\Models\Post $post,
                                                    $user_id)
    {
        $notifications = [];

        $unread_notifications = Notifications::select('recipient_id')
                                ->where('group_id', $group_id)
                                ->where('thread_id', $thread_id)
                                ->where('type', 'reply')
                                ->where('read', '0')
                                ->get()->toArray();

        if ($unread_notifications) {
            $unread_notifications = array_column($unread_notifications, 'recipient_id');
        }

        foreach ($user_list as $recipient_id) {
            // if this user already have an unread notifications of this thread, don't send again
            if (!empty($unread_notifications) && in_array($recipient_id, $unread_notifications)) {
              //  continue;
            }

            $notifications[] = [
                'recipient_id' => $recipient_id,
                'user_id' => $user_id,
                'group_id' => $group_id,
                'thread_id' => $thread_id,
                'post_id' => $post->id,
                'type' => 'reply',
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }

        if ($notifications){
            Notifications::insert($notifications);
        }

        return $notifications;
    }

    public static function addLikeNotifications(\App\Models\Post $post, $user_id)
    {
        Notifications::create([
            'recipient_id' => $post->user_id,
            'user_id' => $user_id,
            'group_id' => $post->group_id,
            'thread_id' => $post->thread_id,
            'post_id' => $post->id,
            'type' => 'like',
            'created_at' => date("Y-m-d H:i:s"),
        ]);

    }

    public static function addReportNotifications(\App\Models\Post $post, $recipient_id, $user_id)
    {
        Notifications::create([
            'recipient_id' => $recipient_id,
            'user_id' => $user_id,
            'group_id' => $post->group_id,
            'thread_id' => $post->thread_id,
            'post_id' => $post->id,
            'type' => 'flag',
            'created_at' => date("Y-m-d H:i:s"),
        ]);
    }

    public static function addAirdropNotifications($group_id, $recipient_id, $user_id, $msg)
    {
        Notifications::create([
            'recipient_id' => $recipient_id,
            'user_id' => $user_id,
            'group_id' => $group_id,
            'msg' => $msg,
            'type' => 'airdrop',
            'created_at' => date("Y-m-d H:i:s")
        ]);
    }

}