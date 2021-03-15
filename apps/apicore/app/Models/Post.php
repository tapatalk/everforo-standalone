<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class  Post extends Model {
    protected $table = 'posts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'id';
    protected $fillable = [
        'thread_id', 'user_id', 'content', 'ipfs', 'create_time', 'parent_id', 'group_id', 
        'nsfw', 'nsfw_score', 'deleted', 'deleted_by',
    ];

    protected $appends = ['total_likes', 'total_report'];

    public function user()
    {
        return $this->hasOne('App\User',"id","user_id")->select(['id','name','photo_url']);
    }

    public function deletedBy()
    {
        return $this->hasOne('App\User', "id", "deleted_by")->select(['id','name']);
    }

    public function likes()
    {
        $data = $this->hasMany('App\Models\Like',"post_id","id")->select(['id', 'user_id', 'post_id'])->orderBy('updated_at', 'desc');
        if($data){
            return $data;
        } else {
            return Array();
        }
    }

    public function flags()
    {
        $data = $this->hasMany('App\Models\Report',"post_id","id")->select(['id', 'user_id', 'post_id'])->orderBy('id', 'desc');
        if($data){
            return $data;
        } else {
            return Array();
        }
    }

    public function thread(){
        return $this->belongsTo('App\Models\Thread','thread_id','id');
    }

    /**
     * total likes accessor
     */
    public function getTotalLikesAttribute()
    {
        return $this->hasMany('App\Models\Like')->wherePostId($this->id)->count();
    }

    /**
     * total report accessor
     */
    public function getTotalReportAttribute()
    {
        return $this->hasMany('App\Models\Report')->wherePostId($this->id)->count();
    }

    public function group(){
        return $this->belongsTo('App\Models\Group','group_id','id');
    }

    public function attachedFiles()
    {
        $data = $this->hasMany('App\Models\AttachedFiles',"post_id","id")->select(['id', 'post_id', 'url', 'name', 'size', 'mime_type']);
        if($data){
            return $data;
        } else {
            return Array();
        }
    }

}
