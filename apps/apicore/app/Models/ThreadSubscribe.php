<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  ThreadSubscribe extends Model 
{
    use SoftDeletes;

    protected $table = 'thread_subscribe';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'thread_id',
    ];
}