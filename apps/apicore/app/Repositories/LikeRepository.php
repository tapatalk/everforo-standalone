<?php

namespace App\Repositories;

use App\Models\Like;

/**
 * 
 */
class LikeRepository
{
    /**
     * 
     */
    public function likePost($user_id, $post_id, $group_id , $reciver_id) {

        $send_notification = false;
        $like = Like::withTrashed()
                ->where('user_id', $user_id)
                ->where('post_id', $post_id)
                ->first();

        if ($like) {
            if ($like->trashed()){
                $like->restore();
            }
        } else {
            $send_notification = true;
            $like = Like::create([
                'user_id' => $user_id,
                'post_id' => $post_id,
                'group_id' => $group_id,
                'reciver_id' => $reciver_id
            ]);
        }

        return [$like, $send_notification];
    }

    /**
     * 
     */
    public function unLikePost($user_id, $post_id) {

        return Like::where('user_id', $user_id)
                    ->where('post_id', $post_id)
                    ->delete();
    }

    /**
     * 
     */
    public function userList($post_id, $group_id)
    {
        return Like::with('user')
                    ->select(array('likes.*','group_ban_users.id as is_ban'))
                    ->where('likes.post_id', $post_id)
                    ->join('group_ban_users', function($join) use ($group_id){
                        $join->on('group_ban_users.user_id','=','likes.user_id')
                            ->where('group_ban_users.group_id','=',$group_id)
                            ->whereNull('group_ban_users.deleted_at');
                    }, null,null,'left')
                    ->orderBy('likes.updated_at', 'desc')
                    ->get();
    }
}