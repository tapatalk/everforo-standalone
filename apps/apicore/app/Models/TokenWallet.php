<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenWallet extends Model
{
    use SoftDeletes;

    protected $table = 'token_wallet';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','user_id','group_id','token_id','balance'
        ];



}
