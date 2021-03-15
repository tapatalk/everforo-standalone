<?php

namespace App\Repositories;

use DB;

use App\Models\Thread;

class ThreadRepository
{
    public function isUncategorized($group_id) {
        return Thread::where(function ($query) {
            return $query->where('category_index_id', '-1')->orWhereNull('category_index_id');
        })->where('group_id', $group_id)->count();
    }
}