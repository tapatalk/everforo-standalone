<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  GroupAdmin extends Model
{
    use SoftDeletes;

    protected $table = 'group_admin';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'user_id', 'level'
    ];

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }
}
