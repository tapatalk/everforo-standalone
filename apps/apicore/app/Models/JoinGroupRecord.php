<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JoinGroupRecord extends Model
{
    use SoftDeletes;

    protected $table = 'join_group_record';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id','user_id','join_msg'
        ];
}
