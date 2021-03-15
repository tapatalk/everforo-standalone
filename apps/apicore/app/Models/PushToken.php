<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class  PushToken extends Model {
    protected $table = 'push_token';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id', 'app_id', 'token','arn'
    ];
  
 
}
