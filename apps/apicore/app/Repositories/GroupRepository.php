<?php

namespace App\Repositories;

use App\Models\Group;
// use App\Models\GroupBanUsers;
// use Illuminate\Support\Facades\Redis;

/**
 * 
 */
class GroupRepository
{
    /**
     * check if is admin
     */
    public function isAdmin($user_id = 0, $group_id = 0, $group = [])
    {
        if(empty($group)){
            $group = Group::find($group_id);
        }

        if (!$group) {
            return false;
        }

        if ($group->owner > 0 && $group->owner == $user_id  ) {
            return true;
        }
        return false;
    }

    /**
     * check name if is occupy
     */
    public function groupNameOccupy($name)
    {

        $new_name_occupy = Group::where("name",$name)->first();

        if(!empty($new_name_occupy)){
            return true;
        }
        return false;
    }

    public function deleteGroup($group){
        if(empty($group)){
            return false;
        }

        $deleted_new_name = $group->name . '_deleted' . rand(0,999);
        if($this->groupNameOccupy($deleted_new_name)){
            throw new Exception('Don\'t worry, please try again');
        }

        $group_updated = tap($group)->update(['name'=>$deleted_new_name])->delete();

        return $group_updated;
    }

    /**
     * get group by name
     * @param string $name
     * @return object|bool
     */
    public function getGroupByName($name)
    {

        $new_name_occupy = Group::where("name",$name)->first();

        if(!empty($new_name_occupy)){
            return $new_name_occupy;
        }
        return false;
    }

    public function getGroupPostCount()
    {
        // select g.id, g.name, count(p.id) as group_post_count from `groups` g left join posts p on p.group_id = g.id group by g.id;
        $group_posts_counts = \DB::table('groups')->leftJoin('posts', 'groups.id', '=', 'posts.group_id')
                ->selectRaw('groups.id, count(posts.id) as group_posts_count')
                ->groupBy('groups.id')->get();
        
        $results = [];

        foreach ($group_posts_counts as $data) {
            $results[$data->id] = $data->group_posts_count;
        }

        return $results;
    }

    public function isOwner($user_id = 0, $group_id = 0, $group = [])
    {
        if(empty($group)){
            $group = Group::find($group_id);
        }

        if (!$group) {
            return false;
        }

        if ($group->owner > 0 && $group->owner == $user_id  ) {
            return true;
        }
        return false;
    }

    public function isOrdinaryAdmin($user_id = 0, $group_id = 0, $group = [])
    {
        
    }
}