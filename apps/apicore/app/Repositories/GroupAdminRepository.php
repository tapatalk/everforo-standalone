<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\GroupFollow;

class GroupAdminRepository
{

    /**
     * add group admin
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function addGroupAdmin($user_id, $group_id)
    {
        $adminCount = GroupAdmin::where('group_id', $group_id)->where('level', 2)->count();
        if ($adminCount >= 5) {
            return false;
        }

        $groupAdmin = GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if (!empty($groupAdmin)) {
            if ($groupAdmin->level == 2) {
                return false;
            } else {
                $groupAdmin->level = 2;
                $groupAdmin->save();
                return true;
            }
        }

        $res = GroupAdmin::create([
                                'group_id' => $group_id,
                                'user_id' => $user_id,
                                'level' => 2,
                            ]);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * add group owner
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function addGroupOwner($user_id, $group_id)
    {
        $res = GroupAdmin::create([
                                'group_id' => $group_id,
                                'user_id' => $user_id,
                                'level' => 1,
                            ]);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete group admin
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function deleteGroupAdmin($user_id, $group_id)
    {
        GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->delete();
    }

    /**
     * delete group moderator
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function deleteGroupModerator($user_id, $group_id)
    {
        GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->delete();
    }

    /**
     * add group admin
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function addGroupModerator($user_id, $group_id)
    {
        $adminCount = GroupAdmin::where('group_id', $group_id)->where('level', 3)->count();
        if ($adminCount >= 5) {
            return false;
        }

        $groupAdmin = GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if (!empty($groupAdmin)) {
            if ($groupAdmin->level == 3) {
                return false;
            } else {
                $groupAdmin->level = 3;
                $groupAdmin->save();
                return true;
            }
        }

        $res = GroupAdmin::create([
                'group_id' => $group_id,
                'user_id' => $user_id,
                'level' => 3,
            ]);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check group admin
     *
     * @param [type] $user_id
     * @param [type] $group_id
     * @return void
     */
    public function isAdmin($user_id, $group_id, $level = '2')
    {
        return GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->where('level', $level)->first() ? true : false;
    }

    /**
     * change group admin
     *
     * @param [type] $user_id
     * @param [type] $admin_id
     * @param [type] $group_id
     * @return void
     */
    public function changeGroupAdmin($user_id, $admin_id, $group_id)
    {
        $groupAdmin = GroupAdmin::where('group_id', $group_id)->where('user_id', $user_id)->where('level', 1)->first();
        if (!$groupAdmin) {
            return false;
        }
        $admin = GroupAdmin::where('group_id', $group_id)->where('user_id', $admin_id)->where('level', 2)->first();
        if (!$admin) {
            return false;
        }
        $admin->level = 1;
        $admin->save();
        $groupAdmin->level = 2;
        $groupAdmin->save();
        return true;
    }

    /**
     * get group admin
     *
     * @param [type] $group_id
     * @param [type] $level
     * @return void
     */
    public function getGroupAdmin($group_id, $level = 0)
    {
        $query = GroupAdmin::where('group_id', $group_id);
        if ($level) {
            $query->where('level', $level);
        }
        return $query->get();
    }

    /**
     * get group member
     *
     * @param [type] $group_id
     * @return array
     */
    public function getGroupMembers($group_id)
    {
        $fields = array('users.name','group_follow.user_id','users.photo_url','group_follow.created_at',
        'group_follow.likes_count', 'group_admin.level');
        return GroupFollow::select($fields)
            ->leftJoin('users', 'users.id', '=', 'group_follow.user_id')
            ->join('group_admin', function ($join) use ($group_id) {
                $join->on('group_admin.user_id', '=', 'group_follow.user_id')
                    ->where('group_admin.group_id', '=', $group_id)
                    ->whereNull('group_admin.deleted_at');
            }, null, null, 'left')
            ->where('group_follow.group_id', '=', $group_id)
            ->orderBy('group_admin.updated_at','asc')
            ->get()
            ->toArray();
        
    }

    public function selectGroupMember($group_id, $user_name)
    {
        $fields = array('users.name','group_follow.user_id','users.photo_url','group_admin.level');
        return GroupFollow::select($fields)
            ->leftJoin('users', 'users.id', '=', 'group_follow.user_id')
            ->join('group_admin', function ($join) use ($group_id) {
                $join->on('group_admin.user_id', '=', 'group_follow.user_id')
                    ->where('group_admin.group_id', '=', $group_id)
                    ->whereNull('group_admin.deleted_at');
            }, null, null, 'left')
            ->where('group_follow.group_id', '=', $group_id)
            ->whereNull('group_admin.deleted_at')
            ->whereNull('group_admin.level')
            ->where('users.name', 'like', '%'.$user_name.'%')
            ->get()
            ->toArray();
    }

    public function selectGroupAdmin($group_id, $user_name)
    {
        $fields = array('users.name','group_follow.user_id','users.photo_url','group_admin.level');
        return GroupFollow::select($fields)
            ->leftJoin('users', 'users.id', '=', 'group_follow.user_id')
            ->join('group_admin', function ($join) use ($group_id) {
                $join->on('group_admin.user_id', '=', 'group_follow.user_id')
                    ->where('group_admin.group_id', '=', $group_id)
                    ->whereNull('group_admin.deleted_at');
            }, null, null, 'left')
            ->where('group_follow.group_id', '=', $group_id)
            ->whereNull('group_admin.deleted_at')
            ->where('group_admin.level', 2)
            ->where('users.name', 'like', '%'.$user_name.'%')
            ->get()
            ->toArray();
    }

    /**
     * get all group admin
     *
     * @param [type] $group_id
     * @return void
     */
    public function getAllGroupAdmin($group_id)
    {
        $featuresRepo = new FeaturesRepo();

        $query = GroupAdmin::where('group_id', $group_id);
        if (!$featuresRepo->isFeature($group_id, 4)) {
            $query->where('level', 1);
        }
        return $query->get();
    }

    /**
     * @param $group_id
     * @param $user_id
     * @param int $level
     * @return bool
     */
    public function checkGroupAdmin($group_id, $user_id, $level = 3)
    {
        $featuresRepo = new FeaturesRepo();
        if (!$featuresRepo->isFeature($group_id, 4) || $level == 1) {
            if ($this->isAdmin($user_id, $group_id, 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($level == 2) {
                if ($this->isAdmin($user_id, $group_id, 2) || $this->isAdmin($user_id, $group_id, 1)) {
                    return true;
                } else {
                    return false;
                }
            }
            if ($level == 3) {
                if ($this->isAdmin($user_id, $group_id, 3) || $this->isAdmin($user_id, $group_id, 2) || $this->isAdmin($user_id, $group_id, 1)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

}