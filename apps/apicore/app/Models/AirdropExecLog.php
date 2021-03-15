<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirdropExecLog extends Model
{
    protected $table = 'airdrop_exec_log';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_id', 'transaction_id','count','user_id','group_id'
    ];

    public $timestamps = false;

}
