<?php

namespace App\Repositories;

use App\Models\TokenWallet;
use App\Repositories\TokenTransactionRepo;

class TokenWalletRepo
{

    public function __construct()
    {

    }


    public function createWallet($user_id = 0 , $group_id = 0 , $token_id, $balance = 0)
    {
        if( empty($token_id)) {
            return false;
        }

        if($user_id Xor $group_id ){

            $wallet = TokenWallet::withTrashed()
                ->where('user_id',$user_id)
                ->where('group_id',$group_id)
                ->where('token_id',$token_id)
                ->first();

            if ($wallet) {
                if ($wallet->trashed()){
                    $wallet->restore();
                }
            } else {

                $wallet = TokenWallet::create([
                    'user_id' => $user_id,
                    'group_id' => $group_id,
                    'token_id' => $token_id,
                    'balance' => $balance
                ]);
            }
            return $wallet;
        }

        return false;
    }

    // 有user_id就是user_wallet 有group_id就是group_wallet
    public function getWallet($user_id = 0, $group_id = 0, $token_id){
        if( empty($token_id) ) {
            return false;
        }

        if($user_id Xor $group_id ){
            $wallet = TokenWallet::where('user_id',$user_id)
                ->where('group_id',$group_id)
                ->where('token_id',$token_id)
                ->first();
            return $wallet;
        }

        return false;
    }

    // get erc20token wallet
    public function getAllERC20TokenWallet($user_id = 0){
        if( empty($user_id) ) {
            return false;
        }

        $wallets = TokenWallet::where('user_id',$user_id)
            ->whereNull('deleted_at')
            ->join('erc20_token','erc20_token.id','=','token_wallet.token_id')
            ->select(
                'token_wallet.id',
                'token_wallet.token_id',
                'token_wallet.balance',
                'erc20_token.name',
                'erc20_token.symbol',
                'erc20_token.logo',
                'erc20_token.decimal',
                'erc20_token.allow_import as is_import'
            )
            ->get()
            ->all();

        if(empty($wallets)){
            return [];
        }

        foreach($wallets as $wallet){
            $wallet->balance = TokenTransactionRepo::getRealBalance($wallet->balance, $wallet->decimal);
        }

        return $wallets;

    }

    public function getWalletWithToken($user_id = 0, $group_id = 0, $token_id){
        if( empty($token_id) ) {
            return false;
        }

        if($user_id Xor $group_id ){
            $wallet = TokenWallet::where('user_id',$user_id)
                ->select('token_wallet.*','erc20_token.name','erc20_token.decimal','erc20_token.symbol')
                ->where('group_id',$group_id)
                ->where('token_id',$token_id)
                ->join('erc20_token','token_wallet.token_id','erc20_token.id')
                ->first();
            return $wallet;
        }

        return false;
    }


    public function getByTokenWalletId($wallet_id) {

        return TokenWallet::where('token_wallet.id', $wallet_id)
            ->select('token_wallet.*','erc20_token.name','erc20_token.decimal','erc20_token.symbol')
                            ->whereNull('token_wallet.deleted_at')
                            ->join('erc20_token','token_wallet.token_id','erc20_token.id')
                            ->first();
    }

    public function getUserWallet($user_id, $token_id) {
        
        return Tokenwallet::where('user_id',$user_id)
                            ->where('token_id',$token_id)
                            ->Join('erc20_token','token_wallet.token_id', '=', 'erc20_token.id')
                            ->first();
    }

}