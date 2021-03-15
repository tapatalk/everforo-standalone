<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenWalletLog extends Model
{
    protected $table = 'token_wallet_log';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id','transaction_id','origin_balance','new_balance','count'
        ];

    public $timestamps = false;

}
