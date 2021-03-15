<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Erc20Token extends Model
{
    protected $table = 'erc20_token';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'decimal', 'contract_address', 'transaction','symbol', 'logo','created_at','updated_at',
    ];
    
}
