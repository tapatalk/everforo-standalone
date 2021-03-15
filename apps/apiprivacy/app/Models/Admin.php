<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class  Admin extends Model {
    protected $table = 'admins';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 
 	
 	  protected $fillable = [
        'id', 'user_id'
    ];

}
