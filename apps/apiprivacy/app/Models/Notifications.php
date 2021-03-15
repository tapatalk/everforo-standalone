<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipient_id', 'user_id', 'group_id', 'msg', 'type'
    ];
}
