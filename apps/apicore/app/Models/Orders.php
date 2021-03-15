<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Orders extends Model
{
    use SoftDeletes;

    protected $table = 'orders';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','order_id','user_id','product_id','order_total','currency','status','token',
        'user_agent','extra_info','created_at','updated_at','group_id','related_id','deleted_at'
        ];



}
