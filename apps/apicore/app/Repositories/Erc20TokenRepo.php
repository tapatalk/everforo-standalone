<?php

namespace App\Repositories;

use App\Models\Erc20Token;
use App\Models\TokenWallet;
use App\Models\GroupErc20Token;
use App\Models\AirdropJob;
use Illuminate\Support\Facades\Redis;

class Erc20TokenRepo
{

    private $_redis;

    //maybe bcpow used more memory
//    protected $decimal_ary =[
//            0 => '1'  ,
//            1 => '10'  ,
//            2 => '100'  ,
//            3 => '1000'  ,
//            4 => '10000'  ,
//            5 => '100000'  ,
//            6 => '1000000'  ,
//            7 => '10000000'  ,
//            8 => '100000000'  ,
//            9 => '1000000000'  ,
//            10 => '10000000000'  ,
//            11 => '100000000000'  ,
//    ];

    public function __construct()
    {
        $this->_redis = Redis::connection();
    }

    public function enableGroupErc20Token($group_id)
    {
        $token = GroupErc20Token::select()
                    ->where('group_id', $group_id)
                    ->first();

        if ($token) {
            tap($token)->update(['status' => 1]);
        }else {
            $token = GroupErc20Token::create([
                'group_id' => $group_id,
                'status' => 1,
            ]);
        }

        return $token;
    }


    public function enableGroupErc20TokenImport($group_id, $token_id)
    {
        $token = GroupErc20Token::withTrashed()
                                ->where('group_id', $group_id)
                                ->where('token_id', $token_id)
                                ->first();
        $is_new = true;
        if ($token) {
            $is_new = false;
            if ($token->trashed()) {
                $token->restore();
            }
        } else {
            GroupErc20Token::create([
                'group_id' => $group_id,
                'token_id' => $token_id,
                'status' => 4,
                'is_import' => 1,
            ]);
            $token = GroupErc20Token::where('group_id', $group_id)
                ->where('token_id', $token_id)
                ->first();
        }

        return [
            'token'   =>$token,
            'is_new'  =>$is_new
        ];
    }


    public function getToken($group_id){
        $token = Erc20Token::with()
            ->where('group_id', $group_id)
            ->first();

        return $token;
    }

    //目前每个group只支持一种 token
    public function getGroupErc20Token($group_id)
    {
        if (empty($group_id)) {
            return false;
        }

        $erc20token = GroupErc20Token::with('erc20_token')
            ->where('group_id',$group_id)
            ->first();

        if(empty($erc20token) || empty($erc20token->erc20_token)){
            return false;
        }

        return $erc20token;
    }


    public function getErc20TokenImportList($offset = 0){

        $page_length = 20;
        
        $query = Erc20Token::where('allow_import',1)
            ->orderBy('created_at', 'asc');

        $tokenlist = $query->offset($offset)->limit($page_length)->get()->toArray();

        return $tokenlist;

    }

    public function getErc20Token($group_id, $user_id = 0){
        if (empty($group_id)) {
            return false;
        }


        $erc20token = \DB::table('erc20_token')
            ->join('token_wallet', 'erc20_token.id', '=', 'token_wallet.token_id')
            ->where('token_wallet.user_id', $user_id)
            ->where('erc20_token.group_id', $group_id)
            ->first();

        if(empty($erc20token)){
            return false;
        }

        return $erc20token;
    }

    public function getGroupErc20TokenBalanceCache($group_id){

        $erc20token = $this->_redis->hgetall("erc:" . $group_id);
        //bal = balance  | dec decimal
        if($erc20token && isset($erc20token['bal']) && isset($erc20token['dec'] )){
            $erc20token['balance']  = $erc20token['bal'];
            $erc20token['decimal']  = $erc20token['dec'];
            return $erc20token;
        }
        return false;
    }

    public function setGroupErc20TokenBalanceCache($token_redis, $group_id){

        if(isset($token_redis['balance']) && isset($token_redis['decimal'])  ){
            $this->_redis->hMset("erc:" . $group_id,
                [
                    'bal' => $token_redis['balance'],
                    'dec' => $token_redis['decimal']
                ] );
        }

    }

    public function getUserErc20TokenBalanceCache($group_id, $user_id){

        $erc20token = $this->_redis->hmget("erc_user:" . $group_id , [$user_id]);

        if($erc20token && isset($erc20token[$user_id]) ){
            return $erc20token[$user_id];
        }

        return false;

    }

    public function setUserErc20TokenBalanceCache($token_redis, $group_id){

        $this->_redis->hMset("erc_user:" . $group_id, $token_redis );

    }

    public function getGroupDecimal($group_id){

        $token_redis = $this->getGroupErc20TokenBalanceCache($group_id);
        if($token_redis !== false && isset($token_redis['decimal'])){
            return $token_redis['decimal'];
        }

    }

    public static function checkBalanceCanBeDecrease($balance, $count){
        $result = bcsub($balance, $count);
        if(strpos($result,'-') === 0){
            return false;
        }
        return true;
    }

    public function deleteImported($group_id) {
        $group_token = GroupErc20Token::where('group_id',$group_id)->first();

        if ($group_token && $group_token->token_id) {
            GroupErc20Token::where('group_id',$group_id)->delete();
            TokenWallet::where('group_id',$group_id)->where('token_id',$group_token->token_id)->delete();
//            Erc20Token::where('id', $group_token->token_id)->delete();
//            AirdropJob::where('group_id',$group_id)->where('token_id',$group_token->token_id)->delete();
            AirdropJob::where('group_id',$group_id)->delete();
        }

        return true;
    }

    public function createERC20TokenAfterPaypalconfirm($order){

        $token = GroupErc20Token::where('token_id', $order->related_id)->first();

        if(!$token || $token->status != 1){
            return ['error'=>''];
        }

        tap($token)->update(['status'=>2]);

            //create wallet wait for paypal success
        $token_wallet = TokenWallet::create([
            'group_id'  => $token->group_id,
            'token_id'  => $token->token_id,
            'balance'   => TokenTransactionRepo::getTableBalance($token->blockchain_balance, $token->decimal)
            ]);


        if(!$token_wallet || !isset($token_wallet->id)){
            throw new \Exception('please try again later');
        }

        if (env('APP_ENV') === 'local') {
            $result = exec('php ' .  base_path('script/test.php'));
        } else {
            \Log::info('cmd');
            $cmd = 'python3 ' .  base_path('script/create_erc20token.py') . " " . $token->id . " > " . base_path('storage/logs/create_erc20token.error.log') . " 2>&1 &";
            \Log::error($cmd);
            exec( $cmd );
        }

    }

    public static function cancelCreateToken($order){
        $grouptoken = GroupErc20Token::where('token_id', $order->related_id)->first();
        if(!$grouptoken || empty($grouptoken->id)){
            return ['error'=>'missing group token'];
        }

        tap($grouptoken)->delete();
        Erc20Token::where('id',$order->related_id)->delete();
        TokenWallet::where('token_id',$order->related_id)->delete();
    }

//    public function calculationBalance($erc20token){
//        if(empty($erc20token)
//        || !isset($erc20token['balance'])
//        || !isset($erc20token['decimal'])
//        ){
//         return false;
//        }
//
//        return bcdiv(
//            $erc20token['balance'],
////            $this->decimal_ary[$erc20token['decimal']]
//            bcpow('10',$erc20token['decimal'])
//            );
//
//    }

    public static function check_decimal_part_error($count, $decimal){
        if($count == 0){
            return 'can\'t be 0';
        }

        $str_count = substr_count($count,'.');
        if( ($str_count != 0 && $str_count != 1) || ($str_count != 0 && $decimal == 0) ){
            return 'number format is wrong ';
        }

        if($decimal){
            $decimal_part = isset(explode(".",$count)[1]) ? explode(".",$count)[1] : '';
            if(strlen($decimal_part) > $decimal){
                return 'Only allow '.$decimal.' decimal places';
            }
        }

        return false;
    }


}