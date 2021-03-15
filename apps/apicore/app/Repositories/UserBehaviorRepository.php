<?php


namespace App\Repositories;

use App\Models\UserBehaviorRecord;
use App\User;
use Illuminate\Support\Facades\Log;

class UserBehaviorRepository
{
    //time limit of update
    const UPDATE_LIMIT_TIME = 60;
    const UPDATE_CHECK_TIME = 300;

    /**
     * update active
     *
     * @param integer $uid
     * @return void
     */
    public static function updateActive($uid = 0){
        if ($uid <= 0) {
            return false;
        }
        //select from log
        $log = UserBehaviorRecord::where('user_id',$uid)->first();
        if (empty($log)) {
            try {
                return UserBehaviorRecord::create([
                    'user_id' => $uid,
                    'last_login' => null,
                ]);
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
                return false;
            }
        }
        $time = time();
        $last_updated_at = strtotime($log->updated_at);
        if ($time - $last_updated_at < self::UPDATE_LIMIT_TIME) {
            return true;
        }
        $result = UserBehaviorRecord::where('user_id',$uid)->update(['updated_at'=>date('Y-m-d H:i:s')]);
        return $result;
    }

    /**
     * Undocumented function
     *
     * @param integer $uid
     * @return void
     */
    public static function updateLastLogin($uid = 0){
        if ($uid <= 0) {
            return false;
        }
        //select from log
        $log = UserBehaviorRecord::where('user_id',$uid)->first();
        if (empty($log)) {
            try {
                return UserBehaviorRecord::create([
                    'user_id' => $uid,
                    'last_login' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
                return false;
            }
        }
        $result = UserBehaviorRecord::where('user_id',$uid)->update(['last_login'=>date('Y-m-d H:i:s')]);
        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $email
     * @return void
     */
    public static function updateLastLoginByEmail($email){
        $user = User::where('email', $email)
                        ->first();
        //select from log
        $log = UserBehaviorRecord::where('user_id',$user->id)->first();
        if (empty($log)) {
            try {
                return UserBehaviorRecord::create([
                    'user_id' => $user->id,
                    'last_login' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
                return false;
            }
        }
        $result = UserBehaviorRecord::where('user_id',$user->id)->update(['last_login'=>date('Y-m-d H:i:s')]);
        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $user_id
     * @return void
     */
    public static function checkUserOnline($user_id)
    {
        $log = UserBehaviorRecord::where('user_id',$user_id)->first();
        if ($log && strtotime($log->updated_at) + self::UPDATE_CHECK_TIME > time()) {
            return 1;
        }
        return 0;
    }

    /**
     * Undocumented function
     *
     * @param [type] $user_id
     * @return void
     */
    public static function getUserLastSeen($user_id)
    {
        $log = UserBehaviorRecord::where('user_id',$user_id)->first();
        if ($log && $log->updated_at) {
            return date('Y-m-d H:i:s', strtotime($log->updated_at));
        }
        return false;
    }

} 