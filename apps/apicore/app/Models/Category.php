<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class  Category extends Model {

    use SoftDeletes;

    protected $table = 'categorys';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */	
    protected $fillable = [
        'name', 'group_id', 'order', 'category_id',
    ];
 	
}
