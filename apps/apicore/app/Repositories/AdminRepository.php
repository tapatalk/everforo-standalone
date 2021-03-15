<?php

namespace App\Repositories;

use App\Models\Admin;

/**
 * 
 */
class AdminRepository
{
    /**
     * add a new report to a post
     */
    public function isSuperAdmin($user_id) 
    {
//        $data = Admin::where('user_id', $user_id)->first();
//        return $data ? 1 : 0;
        return 0;
    }

}