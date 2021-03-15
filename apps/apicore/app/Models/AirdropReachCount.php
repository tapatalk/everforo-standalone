<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirdropReachCount extends Model
{
    protected $table = 'airdrop_reach_count';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_id','user_id','group_id','reach_count'
    ];

}
