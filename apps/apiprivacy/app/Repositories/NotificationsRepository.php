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

    public static function addJoinRequestNotifications($group_id, $recipient_list, $user_id, $msg)
    {
        $notifications = [];

        foreach ($recipient_list as $recipient_id) {

            $notifications[] = [
                'recipient_id' => $recipient_id,
                'user_id' => $user_id,
                'group_id' => $group_id,
                'thread_id' => 0,
                'post_id' => 0,
                'type' => 'join_request',
                'created_at' => date("Y-m-d H:i:s"),
                'msg' => $msg,
            ];
        }
// ALTER TABLE `everforo`.`notifications`
// CHANGE COLUMN `type` `type` ENUM('reply', 'like', 'flag', 'join_request') COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL ;

        if ($notifications){
            Notifications::insert($notifications);
        }

        return $notifications;
    }

}
