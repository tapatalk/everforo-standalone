<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class  ThreadTrack extends Model {
    protected $table = 'threads_track';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   


	  protected $fillable = [
        'thread_id', 'group_id','user_id'
    ];
}
