<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InviteMember extends Model
{
    use SoftDeletes;

    protected $table = 'invite_member';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id', 'group_id', 'email', 'message'
    ];

    public function group()
    {
        return $this->hasOne('App\Models\Group', "id",  "group_id");
    }
}
