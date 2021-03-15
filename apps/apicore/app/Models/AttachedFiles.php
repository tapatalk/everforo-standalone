<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttachedFiles extends Model {

    use SoftDeletes;

    protected $table = 'attached_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 	protected $fillable = [
        'thread_id', 'post_id', 'group_id', 'ipfs',
    ];

}
