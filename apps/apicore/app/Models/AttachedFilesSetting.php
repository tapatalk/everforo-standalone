<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachedFilesSetting extends Model {

    protected $table = 'attached_files_setting';

    protected $primaryKey = 'group_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 	protected $fillable = [
        'allow_everyone', 'allow_post',
    ];

}
