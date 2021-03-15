<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Redis;

use App\Models\BlockUsers;
use App\Models\GroupBanUsers;

/**
 * 
 */
class BanUsersRepository
{

    /**
     * get ban cache key
     * @param int $group_id
     * @return string
     */
    private static function getBanCacheKey($group_id)
    {
        return "group-ban:" . $group_id;
    }

    /**
     * deleted ban cache
     * @param int $group_id
     * @param int $user_id
     * @return null
     */
    private static function delBanCache($group_id, $user_id)
    {
        return Redis::connection()->srem(self::getBanCacheKey($group_id), $user_id);
    }

    /**
     * add ban cache
     * @param $group_id
     * @param $user_id
     * @return int
     */
    private static function addBanCache($group_id, $user_id)
    {
        return Redis::connection()->sadd(self::getBanCacheKey($group_id), $user_id);
    }

    /**
     * check user status
     * @param int $user_id
     * @param int $group_id
     * @return int
     */
    public function checkUserBan($user_id, $group_id)
    {
        $redis = Redis::connection();
        $cache_key = self::getBanCacheKey($group_id);

        //Used to ensure that the cache must exist
        self::getGroupBanUser($group_id);
        return $redis->sismember($cache_key, $user_id);
    }

    public static function getGroupBanUser($group_id)
    {
        $redis = Redis::connection();
        $cache_key = self::getBanCacheKey($group_id);
        if (empty($redis->smembers($cache_key))) {
            $res = GroupBanUsers::select('user_id')
                ->where('group_id', $group_id)
                ->get()->toArray();
            if (!$res) {
                $res = [null];
            } else {
                $res = array_column($res, 'user_id');
            }
            // cache key
            array_unshift($res, $cache_key);
            call_user_func_array(array($redis, "sadd"), $res);
        }
        return $redis->smembers($cache_key);
    }

    /**
     * get group ban num
     * @param int $group_id
     * @return int
     */
    public function getGroupBanUserNum($group_id)
    {
        $res = GroupBanUsers::where('group_id', $group_id)
            ->count();
        return $res ? $res : 0;
    }

    /**
     * admin ban user
     * @param int $user_id
     * @param int $group_id
     * @return object | bool
     */
    public function adminBanUser($user_id, $group_id)
    {
        $groupBanUser = GroupBanUsers::withTrashed()
            ->where( 'group_id', $group_id)
            ->where('user_id', $user_id)->first();

        if ($groupBanUser) {
            // if there is a following record and it's soft deleted, restore it
            // if not deleted, do nothing
            if ($groupBanUser->trashed()) {
                $groupBanUser->restore();
            }
        } else {
            $groupBanUser = GroupBanUsers::create([
                'user_id' => $user_id,
                'group_id' => $group_id,
            ]);
        }
        self::addBanCache($group_id, $user_id);
        return $groupBanUser;
    }

    /**
     * admin unban user
     * @param int $user_id
     * @param int $group_id
     * @return object | bool
     */
    public function adminUnBanUser($user_id, $group_id)
    {
        self::delBanCache($group_id, $user_id);
        return GroupBanUsers::where('user_id', $user_id)
                            ->where('group_id', $group_id)->delete();
    }

    /**
     * get be baned group by user id
     * @param $user_id
     * @return array
     */
    public function getBanGroupByUserId($user_id)
    {
        $group = array();
        $res = GroupBanUsers::select('group_id')
            ->where('user_id', $user_id)
            ->get()->toArray();
        if ($res) {
            $group = array_column($res, 'group_id');
        }
        return $group;
    }

}