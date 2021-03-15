<?php

namespace App\Repositories;

use App\Models\FeaturesConfig;
use App\Models\GroupFollow;
use App\Models\GroupLevelSetting;

class GroupFollowRepository {

    /**
     * get user followed group list
     */
    public function userFowllowedGroups($user_id, $group_id_only = false) {

        $data = GroupFollow::with( 'group')
                ->where('user_id', $user_id)
                ->whereNull('deleted_at')->get();

        if (!$group_id_only) {
            return $data;
        }

        return $data ? array_column($data->toArray(), 'group_id') : [];
    }

    /**`
     * follow a group
     */
    public function followGroup($user_id, $group_id)
    {
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
        return $follow;
    }

    /**
     * check if group followed
     */
    public function isGroupFollowed($user_id, $group_id)
    {
        return GroupFollow::where( 'group_id', $group_id)
                ->where('user_id', $user_id)->first();
    }

    /**
     * unfollow a group
     */
    public function unfollowGroup($user_id, $group_id)
    {
        return GroupFollow::where('user_id', $user_id)
                ->where('group_id', $group_id)->delete();
    }

    /**
     * 
     */
    public function totalFollowers($group_id) {
        return GroupFollow::where('group_id', $group_id)->count();
    }

    /**
     * update member likes number
     *
     * @param int $user_id
     * @param int $group_id
     * @param int $num
     *
     * @return bool|int
     */
    public function updateGroupMemberInfo($user_id, $group_id, $num)
    {
        return GroupFollow::where('group_id', $group_id)
            ->where('user_id', $user_id)->increment('likes_count', $num);
    }

    /**
     * check if group can followed
     */
    public function isCanFollow($user_id, $group_id)
    {
        $adminRep = new AdminRepository();
        if ($adminRep->isSuperAdmin($user_id)) {
            return true;
        }
        if ($this->isGroupFollowed($user_id, $group_id)) {
            return true;
        }

        $feature = FeaturesConfig::where('group_id', $group_id)->where('feature_id', 5)->first();
        if (!$feature || !$feature->status) {
            return true;
        }
        
        $groupLevel = GroupLevelSetting::where( 'group_id', $group_id)->first();
        if (!$groupLevel || $groupLevel->joining == 1) {
            return true;
        }
        return false;
    }

}