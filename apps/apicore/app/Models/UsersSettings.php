<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersSettings extends Model {

    protected $table = 'users_settings';

    protected $primaryKey = 'user_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 	protected $fillable = [
        'user_id', 'dark_mode',
    ];

    public $timestamps = false;

}
