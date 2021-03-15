<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  UserBehaviorRecord extends Model
{
    use SoftDeletes;

    protected $table = 'user_behavior_record';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'last_login'
    ];
}
