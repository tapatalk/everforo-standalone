<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class  Attachment extends Model {
    protected $table = 'attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 
 	
 	  protected $fillable = [
        'thread_id', 'post_id', 'group_id','ipfs'
    ];

}
