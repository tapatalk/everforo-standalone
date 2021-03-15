<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenTransaction extends Model
{
    protected $table = 'token_transaction';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_wallet_id','to_wallet_id','count','transaction_id','channel','token_id'
    ];

    public $timestamps = false;

}
