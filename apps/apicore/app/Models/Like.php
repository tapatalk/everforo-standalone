<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  Like extends Model
{
    protected $table = 'likes';
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'post_id', 'group_id', 'reciver_id'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne('App\User',"id","user_id")->select(['id','name','photo_url']);
    }

}
