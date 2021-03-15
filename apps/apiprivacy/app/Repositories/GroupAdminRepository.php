<?php

namespace App\Repositories;

use App\Models\FeaturesConfig;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\GroupFollow;

class GroupAdminRepository
{
    /**
     * get group admin
     *
     * @param [type] $group_id
     * @param [type] $level
     * @return void
     */
    public function getGroupAdmin($group_id, $level = 0)
    {
        $res = FeaturesConfig::select('feature_id')
                ->where('group_id', $group_id)
                ->where('status', 1)
                ->where('feature_id', 4)
                ->first();
        $query = GroupAdmin::where('group_id', $group_id);
        if (!$res) {
            $query->where('level', 1);
        } else if ($level) {
            $query->where('level', $level);
        }
        return $query->get();
    }

}
