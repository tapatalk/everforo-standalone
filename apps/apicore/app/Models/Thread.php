<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Thread extends Model
{
    protected $table = 'threads';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'user_id', 'group_id', 'category_id', 'category_index_id', 'posts_count', 'likes_count','nsfw','no_recommend',
        'first_post_id','ipfs'
    ];

    public function user()
    {
        return $this->hasOne('App\User', "id", "user_id");
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', "id", "group_id");
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post', "thread_id", "id")->limit(10);
    }

    public function category()
    {
        return $this->hasOne('App\Models\Category', "category_id", "category_index_id");
    }

    public function latest_reply()
    {
        return $this->hasOne('App\Models\Post', "thread_id", "id")->orderBy('id', 'desc')->limit(1);
    }

    public function first_post()
    {
        return $this->hasOne('App\Models\Post', "id", "first_post_id")->orderBy('id', 'asc');
    }

    public function subscribe()
    {
        return $this->hasMany('App\Models\ThreadSubscribe', 'thread_id', 'id');
    }

}
