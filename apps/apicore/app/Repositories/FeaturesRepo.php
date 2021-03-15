<?php

namespace App\Repositories;

use App\User;
use App\Models\Features;
use App\Models\FeaturesConfig;
use Illuminate\Support\Facades\Redis;

class FeaturesRepo
{
    protected $user;

    public function __construct()
    {

    }

    /**
     * get Feature cache key
     * @param int $group_id
     * @return string
     */
    private static function getFeatureCacheKey($group_id)
    {
        return "group-feature:" . $group_id;
    }

    /**
     * del Feature Cache
     * @param $group_id
     * @param $user_id
     * @return int
     */
    public static function delFeatureCache($group_id)
    {
        return Redis::connection()->del(self::getFeatureCacheKey($group_id));
    }

    /**
     * check feature
     * @param int $group_id
     * @param int $feature_id
     * @return int
     */
    public function isFeature($group_id, $feature_id)
    {
        $redis = Redis::connection();
        $cache_key = self::getFeatureCacheKey($group_id);
        $cache = $redis->smembers($cache_key);
        if (empty($cache)) {
            $res = FeaturesConfig::select('feature_id')
                ->where('group_id', $group_id)
                ->where('status', 1)
                ->get()->toArray();
            if (!$res) {
                $res = [null];
            } else {
                $res = array_column($res, 'feature_id');
            }
            // cache key
            array_unshift($res, $cache_key);
            call_user_func_array(array($redis, "sadd"), $res);
        }

        return $redis->sismember($cache_key, $feature_id);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function getList($group_id)
    {
        return Features::select(array('features.id', 'features.feature_name', 'features_config.status'))
                ->join('features_config', function ($join) use ($group_id) {
                    $join->on('features.id', '=', 'features_config.feature_id')
                        ->where('features_config.group_id', '=', $group_id);
                }, null, null, 'left')
                ->whereNull('features.deleted_at')
                ->get();
    }

    public function switchFeature($feature_id, $group_id, $status = 1){
        return FeaturesConfig::updateOrCreate(
            ['feature_id' => $feature_id, 'group_id' => $group_id],
            ['status' => $status]
        );
    }

}