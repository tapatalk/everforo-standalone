<?php

namespace App\Repositories;

use App\Models\GroupPin;
use App\Utils\Constants;
use Illuminate\Support\Facades\Redis;

class GroupPinRepository
{
    /**
     * admin pin topic
     * @param int $thread_id
     * @param int $group_id
     * @param int $user_id
     * @param int $pin_type
     * 
     * @return object | bool
     */
    public function adminPinThread($thread_id, $group_id, $user_id, $pin_type)
    {
        if ($pin_type == Constants::GROUP_ADMIN_PIN) {
            GroupPin::where('group_id', $group_id)->where('pin_type', Constants::GROUP_ADMIN_PIN)->delete();
        }
        $groupPinThread = GroupPin::withTrashed()
            ->where('thread_id', $thread_id)->first();

        if ($groupPinThread) {
            $groupPinThread->user_id = $user_id;
            $groupPinThread->pin_type = $pin_type;
            $groupPinThread->deleted_at = null;
            $groupPinThread->save();
        } else {
            $groupPinThread = GroupPin::create([
                'thread_id' => $thread_id,
                'group_id' => $group_id,
                'user_id' => $user_id,
                'pin_type' => $pin_type,
            ]);
        }
        self::delPinCache($group_id, Constants::GROUP_ADMIN_PIN);
        self::delPinCache($group_id, Constants::SUPER_ADMIN_PIN);
        return $groupPinThread;
    }

    /**
     * admin unpin thread
     * @param int $thread_id
     * @return object | bool
     */
    public function adminUnpinThread($group_id, $thread_id)
    {
        self::delPinCache($group_id, Constants::GROUP_ADMIN_PIN);
        self::delPinCache($group_id, Constants::SUPER_ADMIN_PIN);
        return GroupPin::where('thread_id', $thread_id)->delete();
    }

    /**
     * check thread pin
     *
     * @param int $group_id
     * @param int $thread_id
     * @param int $pin_type
     * 
     * @return void
     */
    public function checkThreadPin($group_id, $thread_id, $pin_type)
    {
        $redis = Redis::connection();
        $cache_key = self::getPinCacheKey($group_id, $pin_type);
        $cache = $redis->smembers($cache_key);
        if (empty($cache)) {
            $res = GroupPin::select('thread_id')
                ->where('group_id', $group_id)
                ->where('pin_type', $pin_type)
                ->get()->toArray();
            if (!$res) {
                $res = [null];
            } else {
                $res = array_column($res, 'thread_id');
            }
            // cache key
            array_unshift($res, $cache_key);
            call_user_func_array(array($redis, "sadd"), $res);
        }

        return $redis->sismember($cache_key, $thread_id);
    }

    /**
     * check group pin
     *
     * @param int $group_id
     * @param int $pin_type
     * @return void
     */
    public function checkGroupPin($group_id, $pin_type)
    {
        $redis = Redis::connection();
        $cache_key = self::getPinCacheKey($group_id, $pin_type);
        $cache = $redis->smembers($cache_key);
        if (empty($cache)) {
            $res = GroupPin::select('thread_id')
                ->where('group_id', $group_id)
                ->where('pin_type', $pin_type)
                ->get()->toArray();
            if (!$res) {
                $res = [null];
            } else {
                $res = array_column($res, 'thread_id');
            }
            // cache key
            array_unshift($res, $cache_key);
            call_user_func_array(array($redis, "sadd"), $res);
        }

        return $redis->sismember($cache_key, null);
    }

    /**
     * get pin cache key
     *
     * @param int $group_id
     * @param int $pin_type
     * 
     * @return void
     */
    private static function getPinCacheKey($group_id, $pin_type)
    {
        return "group-pin:" . $group_id . ":" . $pin_type;
    }

    /**
     * delete pin cache
     *
     * @param int $group_id
     * @param int $pin_type
     * 
     * @return void
     */
    private static function delPinCache($group_id, $pin_type)
    {
        return Redis::connection()->del(self::getPinCacheKey($group_id, $pin_type));
    }

    /**
     * get pin user
     *
     * @param int $thread_id
     * @return void
     */
    public function getPinUser($thread_id)
    {
            $res = GroupPin::select('name')
                ->where('thread_id', $thread_id)
                ->leftjoin('users','users.id','=','group_pin.user_id')
                ->first();
            if ($res) {
                $res = $res->toArray();
                return $res['name'];
            }
            return false;
    }
}