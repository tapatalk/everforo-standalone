<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Report extends Model
{
    protected $table = 'report';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id', 'group_id', 'user_id', 'reason', 'created_at',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne('App\User',"id","user_id")->select(['id','name','photo_url']);
    }

    public function post()
    {
        return $this->hasOne('App\Models\Post',"id","post_id");
    }

    public function poster()
    {
        return $this->hasOneThrough(
            'App\User',
            'App\Models\Post',
            'id', // Foreign key on Post table...
            'user_id', // Foreign key on User table...
            'post_id', // Local key on Report table...
            'id' // Local key on Post table...
        )->select(['id','name','photo_url']);
    }

}
