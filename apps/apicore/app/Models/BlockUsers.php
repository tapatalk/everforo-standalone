<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockUsers extends Model {

    protected $table = 'block_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'block_user_id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne('App\User', "id", "user_id");
    }

    public function block_user()
    {
        return $this->hasOne('App\User', "id", "block_user_id");
    }
 	
}
