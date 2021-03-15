<?php

namespace App\Repositories;

use App\Models\BlockUsers;

/**
 * 
 */
class BlockUsersRepository
{
    /**
     * add a new report to a post
     */
    public function addBlockUser($user_id, $block_user_id) 
    {
        try{
            $block_user =  BlockUsers::create([
                'user_id' => $user_id,
                'block_user_id' => $block_user_id,
            ]);
        }catch (\Illuminate\Database\QueryException $e) {
            // 23000 integrity constraint violation, 
            // means the user already in blocked user list, no worries, we can continue
            // otherwise throw it again
            if ($e->getCode() != 23000) {
                throw $e;
            }
        }

        return $this->getUserBlockedUsers($user_id);
    }

        /**
     * add a new report to a post
     */
    public function removeBlockUser($user_id, $block_user_id) 
    {
        $block_user =  BlockUsers::where('user_id', $user_id)
                                    ->where('block_user_id', $block_user_id)
                                    ->delete();

        return $this->getUserBlockedUsers($user_id);
    }

    /**
     * 
     */
    public function getUserBlockedUsers($user_id): array
    {
        $blocked_users = BlockUsers::where('user_id', $user_id)
                                    ->select('block_user_id')
                                    ->get()->toArray();

        if ($blocked_users) {
            $blocked_users = array_column($blocked_users, 'block_user_id');
        }

        return $blocked_users;
    }

}