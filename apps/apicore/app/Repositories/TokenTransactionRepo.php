<?php

namespace App\Repositories;

use App\Models\TokenTransaction;
use App\Models\TokenWallet;
use App\Models\TokenWalletLog;
//use App\Models\TokenGroupWallet;
use App\Models\AirdropExecLog;
use App\Repositories\Erc20TokenRepo;
use App\Utils\Constants;
use Log;

class TokenTransactionRepo
{
    private $_erc20tokenrepo;
    private $_decimal;

    public function __construct()
    {
        $this->_erc20tokenrepo = new Erc20TokenRepo();
        $this->_decimal = 0;
    }

    public function set_decimal($decimal){
        $this->_decimal = $decimal;
    }

    //count is table count not real count
    public function transactionToken($from_wallet_id, $to_wallet_id, $token_id, $count, $channel = 0)
    {

        if((!$from_wallet_id && !$to_wallet_id) || !$token_id || !$count){
            return ['error'=>'missing args'];
        }

        $decrease = $this->decrease_money($from_wallet_id, $count);


        if(!$decrease || empty($decrease) || isset($decrease['error']) ){
            return ['error'=>
                isset($decrease['error']) ? isset($decrease['error']) : 'decrease failed'
            ];
        }

        if($channel != Constants::CHANNEL_ID_WITHDRAW){
            $increase = $this->increase_money($to_wallet_id, $count);
            if(!$increase || empty($increase) || isset($increase['error'])){
                $this->increase_money($from_wallet_id, $count);
                return ['error'=>
                    isset($increase['error']) ? isset($increase['error']) : 'increase failed'
                ];
            }

            $transaction = $this->add_transaction($to_wallet_id, $from_wallet_id, $count, $token_id, $channel);

            if(!$transaction || !isset($transaction['transaction_id'])){
                //log


//            return ['error'=>'add transaction failed'];
            }
            $this->add_token_wallet_change_log($to_wallet_id, $transaction['transaction_id'], $increase['origin_balance'], $increase['new_balance'], $count);
        } else {
            $increase = [];
            $transaction[] = '';
        }

        $this->add_token_wallet_change_log($from_wallet_id, 
            empty($transaction['transaction_id']) ? '' : $transaction['transaction_id'] ,
            $decrease['origin_balance'],
            $decrease['new_balance'], 
            $count);


        return [
            'decrease'=>$decrease,
            'increase'=>$increase,
            'transaction'=> $transaction ? $transaction : [],
        ];

    }


    public function importTransaction($to_wallet_id, $token_id, $count,  $channel = 0, $decimal = 0){
        $increase = $this->increase_money($to_wallet_id, $count);
        if(!$increase || empty($increase) || isset($increase['error'])){
            return ['error'=>
                isset($increase['error']) ? isset($increase['error']) : 'increase failed'
            ];
        }
        $transaction = $this->add_transaction($to_wallet_id, 0, $count, $token_id, $channel);
        if(!$transaction || !isset($transaction->transaction_id)){
            $decrease = $this->decrease_money($to_wallet_id, $count, true);
            //log
            return ['error'=>'add transaction failed'];
        }
        $this->add_token_wallet_change_log($to_wallet_id, $transaction->transaction_id, $increase['origin_balance'], $increase['new_balance'], $count);
        return [
            'increase'=>$increase,
            'transaction'=> $transaction ? [] : $transaction->toArray(),
            ];
    }

    private function add_transaction($to_wallet_id, $from_wallet_id, $count, $token_id, $channel){
        return TokenTransaction::create([
            'from_wallet_id'=>$from_wallet_id,
            'to_wallet_id'=>$to_wallet_id,
            'count'=>$count,
            'channel'=>$channel,
            'token_id'=>$token_id,
            'transaction_id'=>self::createTransactionId(self::channel_to_prefix($channel)),
        ]);
    }

    public function decrease_money($wallet_id, $count , $force_decrease = false ){
        return \DB::transaction(function() use ($wallet_id , $count, $force_decrease) {
            $wallet = TokenWallet::where('id',$wallet_id)->lockForUpdate()->first();
            if(isset($wallet->balance)){
                if(!Erc20TokenRepo::checkBalanceCanBeDecrease($wallet->balance, $count)){
                    return ['error'=>'balance not enough'];
                }
                $newbalance = bcsub($wallet->balance, $count);
                if($newbalance>=0 || $force_decrease){
                    $affected = TokenWallet::where('id',$wallet_id)->update(['balance'=>$newbalance]);
                    if($affected){
                        return [
                            'origin_balance'=>$wallet->balance,
                            'new_balance'=>$newbalance
                        ];
                    } else {
                        return ['error'=>'update failed'];
                    }
                }
            }
        });

    }

    public function increase_money($wallet_id, $count ){
        return \DB::transaction(function() use ($wallet_id , $count ) {
            $wallet = TokenWallet::where('id',$wallet_id)->lockForUpdate()->first();
            if(isset($wallet->balance)){
                $newbalance = bcadd($wallet->balance, $count);
                $affected = TokenWallet::where('id',$wallet_id)->update(['balance'=>$newbalance]);
                if($affected){
                    return [
                        'origin_balance'=>$wallet->balance,
                        'new_balance'=>$newbalance
                    ];
                } else {
                    return ['error'=>'update failed'];
                }
            }
        });
    }

    public function add_token_wallet_change_log($wallet_id, $transaction_id,$origin_balance, $new_balance, $count){
        if($wallet_id && $transaction_id && $origin_balance && $new_balance && $count ){
            TokenWalletLog::create([
                'wallet_id'         => $wallet_id,
                'transaction_id'    => $transaction_id,
                'origin_balance'    => $origin_balance,
                'new_balance'       => $new_balance,
                'count'             => $count,
            ]);
        }
    }

    private function rollback_transaction($transaction){
        TokenTransaction::where('id',$transaction->id)->update(['status'=>2]);
    }

    public static function channel_to_prefix($channel = 0){
        if($channel == 1){  //AIR DROP;
            return 'ADP';
        }
        return 'CM';
    }

    public static function createTransactionId($prefix = '')
    {
        $uId = $prefix . date('Ymd') . hexdec( uniqid(rand(1,9)) ) . rand(1000000,9999999);
        $uId = substr($uId, 0, 32);
        if (strlen($uId) != 32) {
            $uId = str_pad($uId, 32, '0', STR_PAD_RIGHT);
        }
        return $uId;
    }

    public static function getRealBalance($balance, $decimal = 0){
        if($decimal == 0){
            return $balance;
        }
        $decimal = (string)$decimal;

        return bcdiv($balance , bcpow('10',$decimal) , $decimal);
    }

    public static function getTableBalance($balance, $decimal = 0){
        if($decimal == 0){
            return $balance;
        }
        $decimal = (string)$decimal;

        return bcmul($balance , bcpow('10',$decimal) , '0');
    }

}