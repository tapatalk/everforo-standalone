<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturesConfig extends Model
{
    protected $table = 'features_config';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','feature_id','group_id','status','created_at','updated_at'
        ];

    public function group_follow()
    {
        return $this->hasMany('App\Models\GroupFollow', 'group_id', 'group_id');
    }

}
