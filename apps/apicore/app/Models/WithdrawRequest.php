<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawRequest extends Model
{
    use SoftDeletes;

    protected $table = 'withdraw_request';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','wallet_id','user_id','group_id','amount','token_id',
        'to','transactionHash','status','cost','order_id','deleted_at'
        ];



}
