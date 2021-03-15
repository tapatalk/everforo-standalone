<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  GroupPin extends Model
{
    use SoftDeletes;

    protected $table = 'group_pin';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'group_id','thread_id','pin_type'
    ];

    public function group()
    {
        return $this->hasOne('App\Models\Group',"id","group_id");
    }
}
