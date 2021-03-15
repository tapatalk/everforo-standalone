<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class  Group extends Model
{
    use SoftDeletes;

    protected $table = 'groups';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'title', 'cover', 'logo', 'description', 'owner', 'no_recommend', 'super_no_recommend',
    ];

    protected $dates = [
        'deleted_at',
    ];

//    public function erc20_token()
//    {
//        return $this->hasOne('App\Models\Erc20Token',  "group_id", "id");
//    }

    public function group_erc20token()
    {
        return $this->hasOne('App\Models\GroupErc20Token',  "group_id", "id");
    }


    public function token_wallet()
    {
        return $this->hasOne('App\Models\TokenWallet',  "group_id", "id");
    }

}
