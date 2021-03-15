<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ERC20TokenImportLog extends Model
{
    protected $table = 'erc20token_import_log';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','transactionHash','from','to','token_id','value','status'
        ];

    public $timestamps = false;

}
