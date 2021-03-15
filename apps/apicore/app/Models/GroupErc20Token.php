<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupErc20Token extends Model
{

    use SoftDeletes;

    protected $table = 'group_erc20token';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','group_id', 'token_id', 'status', 'blockchain_balance', 'public_key', 'is_import',
    ];

    public function erc20_token()
    {
        return $this->belongsTo ('App\Models\Erc20Token',  "token_id", "id");
    }

}
