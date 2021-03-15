<?php


namespace App\Repositories;

use App\Mail\InviteMemberMail;
use App\Mail\sendApproveMail;
use App\Models\Group;
use App\Models\GroupBanUsers;
use App\Models\GroupFollow;
use App\Models\GroupLevelSetting;
use App\Models\InviteMember;
use App\Models\UserCategoryActive;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Models\GroupMemberActive;
use App\Models\JoinGroupRecord;
use Illuminate\Support\Facades\DB;

class MemberListRepository
{

    /**
     * @param $group_id
     * @param $offset
     * @param $page_length
     * @return array
     */
    public function getMemberList($group_id, $offset, $page_length, $filter)
    {
        $notOnline = 0;
        $count = 0;
        $groutFollow = new GroupFollow();
        $fields = array(DB::raw('users.name,group_follow.user_id,users.photo_url,group_follow.created_at,
        group_follow.likes_count,case when group_admin.level is null then 5 else group_admin.level end as is_admin'));
        $query = $groutFollow
            ->leftJoin('users', 'users.id', '=', 'group_follow.user_id')
            //admin is first
            ->join('group_admin', function ($join) use ($group_id) {
                $join->on('group_admin.user_id', '=', 'group_follow.user_id')
                    ->where('group_admin.group_id', '=', $group_id)
                    ->whereNull('group_admin.deleted_at');
            }, null, null, 'left')
            ->where('group_follow.group_id', '=', $group_id);
        if ($filter === 'active') {
            $date = date('Y-m-d H:i:s', time() - 3600 * 24 * 30);
            $query = $query->join('group_member_active', function ($join) use ($group_id) {
                $join->on('group_member_active.user_id', '=', 'group_follow.user_id')
                    ->where('group_member_active.group_id', '=', $group_id);
            }, null, null, 'left')
            ->where('group_member_active.updated_at', '>', $date)
            ->orderBy('group_member_active.updated_at', 'desc');
            $fields[] = 'group_member_active.updated_at';
        } else if ($filter === 'online') {
            $query = $query->join('user_behavior_record', function ($join) {
                $join->on('user_behavior_record.user_id', '=', 'group_follow.user_id');
            }, null, null, 'left')
            ->orderBy('user_behavior_record.updated_at', 'desc');
            $fields[] = 'user_behavior_record.last_login';
            $fields[] = 'user_behavior_record.updated_at';
        }
        if ($filter !== 'online') {
            $count = $query->count();
        }
        $onlineQuery = clone $query;
        $query = $query->select($fields);

        $list = $query->orderBy('is_admin', 'asc')
            ->orderBy('group_follow.created_at', 'desc')
            ->offset($offset)->limit($page_length)
            ->get()
            ->toArray();
        
        $sevenDays = date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 7);
        foreach ($list as $key => $value) {
            if (UserBehaviorRepository::checkUserOnline($value['user_id'])) {
                $list[$key]['online'] = true;
            } else {
                $list[$key]['online'] = false;
            }
            if ($filter === 'online') {
                if ($value['updated_at'] > $sevenDays) {
                    $list[$key]['seven_days'] = true;
                } else {
                    $list[$key]['seven_days'] = false;
                }
            }
            if ($value['is_admin'] == 5) {
                $list[$key]['is_admin'] = false;
            }
        }

        if ($filter === 'online') {
            $Allcount = $onlineQuery->count();
            $date = date('Y-m-d H:i:s', time() - 300);
            $count = $onlineQuery->where('user_behavior_record.updated_at', '>', $date)->count();
            $notOnline = $Allcount - $count;
        }
        return array('count' => $count, 'list' => $list, 'notOnline' => $notOnline);
    }

    /**
     * @param $group_id
     * @param $offset
     * @param $page_length
     * @return array
     */
    public function getPendingList($group_id, $offset, $page_length)
    {
        $notOnline = 0;
        $count = 0;
        $joinGroupRecord = new JoinGroupRecord();
        $fields = array('users.name','join_group_record.user_id','users.photo_url','join_group_record.created_at','join_group_record.join_msg');
        $query = $joinGroupRecord
            ->leftJoin('users', 'users.id', '=', 'join_group_record.user_id')
            ->where('join_group_record.group_id', '=', $group_id);
        $count = $query->count();
        $query = $query->select($fields);
        $list = $query->orderBy('join_group_record.updated_at', 'desc')
            ->offset($offset)->limit($page_length)
            ->get()
            ->toArray();
        
        return array('count' => $count, 'list' => $list, 'notOnline' => $notOnline);
    }

    /**
     * @param $group_id
     * @param $offset
     * @param $page_length
     * @return mixed
     */
    public function getBanMemberList($group_id, $offset, $page_length)
    {
        $groutBanUsers = new GroupBanUsers();
        $query = $groutBanUsers->select(array('users.name','group_ban_users.user_id','users.photo_url','group_follow.created_at',
            'group_follow.likes_count'))
            ->leftJoin('users', 'users.id', '=', 'group_ban_users.user_id')
            //admin is first
            ->join('group_follow', function ($join) use ($group_id) {
                $join->on('group_ban_users.user_id', '=', 'group_follow.user_id')
                    ->where('group_follow.group_id', '=', $group_id);
            }, null, null, 'left')
            ->where('group_ban_users.group_id', '=', $group_id);
        $count = $query->count();
        $list = $query->orderBy('group_follow.created_at', 'desc')
            ->offset($offset)->limit($page_length)
            ->get()
            ->toArray();
        return array('list' => $list, 'count' => $count);
    }

    /**
     * check invite
     *
     * @param int $group_id
     * @param int $user_id
     * @param string $email
     * @return int
     */
    public static function checkInvite($group_id, $user_id, $email)
    {
        if (!GroupFollow::where( 'group_id', $group_id)->where('user_id', $user_id)->first()) {
            return 1;
        }
        $user = User::select('id')->where('email', $email) ->first();
        if ($user && $user->id && GroupFollow::where('group_id', $group_id)->where('user_id', $user->id)->first()) {
            return 2;
        }
       
        $date = date('Y-m-d H:i:s', time() - 86400);
        $nowDate = date('Y-m-d H:i:s');
        if (InviteMember::where('group_id', $group_id)
                ->where('user_id', $user_id)
                ->where('created_at', '>=', $date)
                ->where('created_at', '<=', $nowDate)
                ->where('email', $email)
                ->first()) {
            return 3;
        }

        $invite = InviteMember::where('group_id', $group_id)
                    ->where('user_id', $user_id)
                    ->where('created_at', '>=', $date)
                    ->where('created_at', '<=', $nowDate)
                    ->get();
        if (count($invite) >= 10) {
            return 4;
        }

        $inviteAll = InviteMember::where('user_id', $user_id)
                    ->where('created_at', '>=', $date)
                    ->where('created_at', '<=', $nowDate)
                    ->get();
        if (count($inviteAll) >= 100) {
            return 4;
        }
    }

    /**
     * add invite member
     *
     * @param int $group_id
     * @param int $user_id
     * @param string $email
     * @param string $message
     * @return void
     */
    public static function addInviteRecord($group_id, $user_id, $email, $message)
    {
        $groupInvite = InviteMember::create([
            'group_id' => $group_id,
            'user_id' => $user_id,
            'email' => $email,
            'message' => $message,
        ]);
        if ($groupInvite) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * send invite member email
     *
     * @param string $group_name
     * @param string $group_title
     * @param string $user_name
     * @param string $email
     * @param string $message
     * @return void
     */
    public static function sendInviteEmail($group_name, $group_title, $group_cover, $user_name, $user_avatar, $email, $message)
    {
        if (!$message) {
            $message = 'Hey! Check out this group!';
        }
        if (!$group_cover) {
            $group_cover = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/img/default_cover.png';
        }
        $send_data['join_url'] = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/join?email=' . $email;
        $send_data['user_name'] = $user_name;
        $send_data['group_title'] = $group_title;
        $send_data['message'] = $message;
        $send_data['user_avatar'] = $user_avatar;
        $send_data['group_cover'] = $group_cover;
        $inviteMail = new InviteMemberMail($send_data);

        if (env('APP_ENV') === 'local') {
            \Log::info($send_data);
            return;
        }

        try{
            Mail::to($email)->send($inviteMail);
        } catch(Exception $e){
            print_r($e);
        }
    }

    /**
     * send invite member email
     *
     * @param string $group_name
     * @param string $group_title
     * @param string $user_name
     * @param string $email
     * @param string $message
     * @return void
     */
    public static function sendApproveEmail($group_name, $group_title, $group_cover, $email)
    {
        $message = 'You\'re now a member of ' . $group_title;
        if (!$group_cover) {
            $group_cover = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/img/default_cover.png';
        }
        $send_data['group_url'] = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $group_name
        . '/all';
        $send_data['message'] = $message;
        $send_data['group_title'] = $group_title;
        $send_data['group_cover'] = $group_cover;
        $inviteMail = new sendApproveMail($send_data);
        if (env('APP_ENV') === 'local') {
            \Log::info($send_data);
            return;
        }

        try{
            Mail::to($email)->send($inviteMail);
        } catch(Exception $e){
            print_r($e);
        }
    }

    /*
     * add member active record
     *
     * @param int $group_id
     * @param int $user_id
     * @return void
     */
    public function addMemberActiveRecord($group_id, $user_id)
    {
        $groupMemberActive = GroupMemberActive::withTrashed()
        ->where( 'group_id', $group_id)
        ->where('user_id', $user_id)
        ->first();
        if ($groupMemberActive) {
            // if there is a following record and it's soft deleted, restore it
            // if not deleted, do nothing
            $groupMemberActive->updated_at = date('Y-m-d H:i:s');
            $groupMemberActive->deleted_at = null;
            $groupMemberActive->save();
        } else {
            $groupMemberActive = GroupMemberActive::create([
                'user_id' => $user_id,
                'group_id' => $group_id,
            ]);
        }
        return $groupMemberActive;
    }

    /**
     * get active num
     *
     * @param int $group_id
     * @return void
     */
    public function getActiveNum($group_id)
    {
        $date = date('Y-m-d H:i:s', time() - 3600 * 24 * 30);
        $query = GroupFollow::where('group_follow.group_id', '=', $group_id)
            ->where('group_member_active.updated_at', '>', $date);
        $query = $query->join('group_member_active', function ($join) use ($group_id) {
            $join->on('group_member_active.user_id', '=', 'group_follow.user_id')
                ->where('group_member_active.group_id', '=', $group_id);
        }, null, null, 'left');
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * get online num
     *
     * @param int $group_id
     * @return void
     */
    public function getOnlineNum($group_id)
    {
        $date = date('Y-m-d H:i:s', time() - 300);
        $query = GroupFollow::where('group_follow.group_id', '=', $group_id)
                    ->where('user_behavior_record.updated_at', '>', $date);
        $query = $query->join('user_behavior_record', function ($join) {
                        $join->on('user_behavior_record.user_id', '=', 'group_follow.user_id');
                    }, null, null, 'left');
        $count = $query->count();
        return $count ? $count : 0;
    }

    public function getPendingNum($group_id)
    {
        $joinGroupRecord = new JoinGroupRecord();
        $query = $joinGroupRecord
            ->leftJoin('users', 'users.id', '=', 'join_group_record.user_id')
            ->where('join_group_record.group_id', '=', $group_id);
        $count = $query->count();
        return $count ? $count : 0;
    }

    public function addAdminLevelSetting($group_id)
    {
        GroupLevelSetting::create([
            'visibility' => 1,
            'joining' => 1,
            'group_id' => $group_id,
        ]);
    }

    public function checkUserJoinRequest($group_id, $user_id)
    {
        $joinGroupRecord = new JoinGroupRecord();
        $query = $joinGroupRecord->where('group_id', $group_id)->where('user_id', $user_id)->first();
        if ($query) {
            return 1;
        } else {
            return 0;
        }
    }

}