<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirdropJob extends Model
{
    use SoftDeletes;

    protected $table = 'airdrop_job';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','group_id','token_id','rule_name','award_count','repeat','condition','days','exec_status','exec_time','type'
    ];

    public function erc20token(){
        return $this->hasOne('App\Models\Erc20Token', "id", "token_id");
    }

}
