<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\FeaturesConfig;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\GroupFollow;
use App\Models\GroupLevelSetting;
use App\Models\JoinGroupRecord;
use App\Models\Notifications;
use App\Models\User;
use App\Models\Users;

use App\Repositories\GroupAdminRepository;
use App\Repositories\NotificationsRepository;

use App\Events\JoinRequestEvent;
use App\Models\GroupBanUsers;
use Event;

class GroupPrivacyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * get group privacy status
     *
     * @return void
     */
    public function getAllPrivacyGroupByUser()
    {
        $user_id = 0;
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
        }
        $group_data = array();
        $sql = FeaturesConfig::select('features_config.group_id')
        ->join('group_level_setting', function($join){
            $join->on('group_level_setting.group_id', '=', 'features_config.group_id');
        }, null, null, 'left')
        ->where('features_config.feature_id', '=', 5)
        ->where('features_config.status', '=', 1)
        ->where('group_level_setting.visibility', '>=', 2)
        ->where('group_level_setting.visibility', '<=', 3);
        if ($user_id) {
            $sql = $sql->whereDoesntHave('group_follow', function ($query) use ($user_id) {
                $query->where('group_follow.user_id', '=', $user_id);
            });
        }
        $res = $sql->get()->toArray();
        if ($res) {
            $group_data = array_column($res, 'group_id');
        }
        return $group_data;
    }

    /**
     * get group privacy status
     *
     * @return void
     */
    public function checkPrivacyGroupStatus()
    {
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
        if (!$group_id) {
            return false;
        }

        $feature = FeaturesConfig::where('group_id', $group_id)->where('feature_id', 5)->first();
        if ($feature && $feature->status) {
            $level = GroupLevelSetting::where('group_id', $group_id)->where('visibility', 3)->first();
            if ($level) {
                if ($user_id) {
                    $group = GroupFollow::where('group_id', $group_id)->where('user_id', $user_id)->first();
                    if ($group) {
                        return true;
                    }
                    $data = Admin::where('user_id', $user_id)->first();
                    if ($data) {
                        return true;
                    }
                }
                return false;
            }
        }
        return true;
    }

    /**
     * get group privacy setting
     *
     * @return void
     */
    public function getGroupPrivacySetting()
    {
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;

        if (!$group_id) {
            return $this->fail(40001);
        }

        $res = GroupLevelSetting::where('group_id', $group_id)->first();
        if ($res) {
            $res = $res->toArray();
        }

        return $this->success($res);
    }

    /**
     * set group privacy setting
     *
     * @return void
     */
    public function setGroupPrivacySetting()
    {
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
        $visibility = isset($_GET['visibility']) ? $_GET['visibility'] : 0;
        $joining = isset($_GET['joining']) ? $_GET['joining'] : 0;
        $res = GroupLevelSetting::withTrashed()->where('group_id', $group_id)->first();
        if ($res) {
            $res->deleted_at = null;
            $res->visibility = $visibility;
            $res->joining = $joining;
            $res->save();
        } else {
            GroupLevelSetting::create([
                'visibility' => $visibility,
                'joining' => $joining,
                'group_id' => $group_id,
            ]);
        }
        return $this->success();
    }

    /**
     * set group privacy setting
     *
     * @return void
     */
    public function joinRequest(
        GroupAdminRepository $groupAdminRep,
        NotificationsRepository $notificationsRep)
    {
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
        $join_msg = isset($_GET['join_msg']) ? $_GET['join_msg'] : '';
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
        if (GroupFollow::where( 'group_id', $group_id)->where('user_id', $user_id)->first()) {
            return $this->fail(40001);
        }
        $res = JoinGroupRecord::withTrashed()->where('group_id', $group_id)->where('user_id', $user_id)->first();

        if ($res) {
            if ($res->deleted_at) {
                $res->deleted_at = null;
                $res->join_msg = $join_msg;
                $res->save();
                return $this->success($res);
            }
            $res->join_msg = $join_msg;
            $res->save();
        } else {
            JoinGroupRecord::create([
                'user_id' => $user_id,
                'join_msg' => $join_msg,
                'group_id' => $group_id,
            ]);
        }
        //send notifction
        $group_admins = $groupAdminRep->getGroupAdmin($group_id);

        if ($group_admins) {
            $group_admins = $group_admins->toArray();

            $group_admins = array_column($group_admins, 'user_id');
        }
        $group = Group::find($group_id);
        $user = Users::find($user_id);

        if($group_admins) {
            // here we add notifications when there is no unread notification for corresonding thread,
            // but we send the event to all subscribers, to upadte the thread in real time
            $msg = $user->name . ' requested to join ' . $group->title;
            $notifications = $notificationsRep->addJoinRequestNotifications($group_id, $group_admins,$user_id, $msg);
// \Log::info('message', [$notifications]);
            $payload = [
                'type' => 'join_request',
                'group_name' => $group->name,
                'thread_id' => 0,
                'post_id' => 0,
                'user_id' => $user->id,
                'user' => [
                    'photo_url' => $user->photo_url,
                    'name' => $user->name,
                ],
                'post_content' => '',
                'post_parent_id' => 0,
                'created_at' => date("Y-m-d H:i:s"),
                'ipfs' => '',
                'attached_files' => [],
                'msg' => $msg,
            ];

            Event::dispatch(new JoinRequestEvent($group_admins, $payload));

        }
        return $this->success($res);
    }

    /**
     * set group privacy setting
     *
     * @return void
     */
    public function ignoreRequest()
    {
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
        $res = JoinGroupRecord::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if ($res) {
            $res->deleted_at = date('Y-m-d H:i:s');
            $res->save();
        }

        $is_ban = GroupBanUsers::where('group_id', $group_id)->where('user_id', $user_id)->count();

        if ($is_ban) {
            return $this->success(['no_msg' => 1]);
        }

        return $this->success();
    }

    /**
     * set group privacy setting
     *
     * @return void
     */
    public function approveRequest()
    {
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
        $res = JoinGroupRecord::where('group_id', $group_id)->where('user_id', $user_id)->first();

        $is_ban = GroupBanUsers::where('group_id', $group_id)->where('user_id', $user_id)->count();

        if ($is_ban && $res) {
            $res->deleted_at = date('Y-m-d H:i:s');
            $res->save();

            return $this->success(['no_msg' => 1]);
        }

        if ($res) {
            $res->deleted_at = date('Y-m-d H:i:s');
            $res->save();
            $follow = GroupFollow::withTrashed()
                    ->where( 'group_id', $group_id)
                    ->where('user_id', $user_id)->first();

            if ($follow) {
                // if there is a following record and it's soft deleted, restore it
                // if not deleted, do nothing
                if ($follow->trashed()) {
                    $follow->restore();
                }
            } else {
                $follow = GroupFollow::create([
                    'user_id' => $user_id,
                    'group_id' => $group_id,
                ]);
            }
        }
        $user = Users::find($user_id);
        $group = Group::where('id', $group_id)->first();
        Notifications::create([
            'recipient_id' => $user_id,
            'user_id' => $user_id,
            'group_id' => $group_id,
            'msg' => 'You\'re now a member of ' . $group->title,
            'type' => 'join'
        ]);
        
        $payload = [
            'type' => 'join',
            'group_name' => $group->name,
            'thread_id' => 0,
            'post_id' => 0,
            'user_id' => $user->id,
            'user' => [
                'photo_url' => $user->photo_url,
                'name' => $user->name,
            ],
            'post_content' => '',
            'post_parent_id' => 0,
            'created_at' => date("Y-m-d H:i:s"),
            'ipfs' => '',
            'attached_files' => [],
            'msg' => 'You\'re now a member of ' . $group->title,
        ];

        Event::dispatch(new JoinRequestEvent([$user->id], $payload));

        $user = User::where('id', $user_id)->first();
        $url = 'http://apicore/api/send_approve_email?email=' . $user->email
                . '&group_name=' . $group->name . '&group_title=' . $group->title
                . '&group_cover=' . $group->cover;
        $res = $this->geturl($url);
        return $this->success();
    }

    /**
     * Format success response
     * @param   array   $data
     * @param   string  $description
     * @return  array
     */
    public function success($data = [], $description = '')
    {
        if (empty($data)) $data = new \stdClass;  // For IOS, needs to return {} instead of null


        array_walk_recursive($data,function(&$item){if($item == null){$item=strval($item);}});

        return [
            'status'      => (bool) true,
            'code'        => (string) 20000,
            'description' => (string) $description,
            'server'      => 'rest',
            'data'        => $data
        ];
    }


    public function noPermission($data = []){
        return self::fail(403,'You have no permission to perform this action.');
    }

    /**
     * Format failure response
     * @param   int(5)  $error_code
     * @param   string  $description  Default: 'Invalid Request'
     * @param   array   $data
     * @return  array
     */
    public function fail($error_code, $description = 'Invalid Request', $data = [])
    {
        if (empty($data)) $data = new \stdClass;  // For IOS, needs to return {} instead of null

        return [
            'status'      => (bool) false,
            'code'        => (string) $error_code,
            'description' => (string) $description,
            'server'      => 'master',
            'data'        => $data
        ];
    }

    /**
     * Undocumented function
     *
     * @param [type] $url
     * @return void
     */
    public function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        return $output;
    }
}
