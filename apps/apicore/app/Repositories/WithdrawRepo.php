<?php

namespace App\Repositories;

use App\Models\Erc20Token;
use App\Models\TokenWallet;
use App\Models\GroupErc20Token;
use App\Models\WithdrawRequest;
use App\Repositories\TokenWalletRepo;
use Illuminate\Support\Facades\Redis;

class WithdrawRepo
{


    public function __construct()
    {

    }

    public function checkWithdraw($amount, $user_id, $token_id)
    {
        $TokenWalletRepo = new TokenWalletRepo();
        $wallet = $TokenWalletRepo->getWalletWithToken($user_id, 0, $token_id);

        if($wallet && isset($wallet->id) ){

            $amount = TokenTransactionRepo::getTableBalance($amount, $wallet['decimal']);

            if(Erc20TokenRepo::checkBalanceCanBeDecrease($wallet->balance, $amount)){

                $WithdrawRequest = $this->checkUnfinishWithdraw($user_id, $token_id);

                if($WithdrawRequest && empty($WithdrawRequest->id)){
                    return ['withdraw'=>$WithdrawRequest];
                }

                return ['wallet'=>$wallet];

            } else {
                return ['error'=>'balance doesn\'t enough'];
            }

        } else {
            return ['error'=>'wallet doesn\'t exist'];
        }

    }


    public function checkUnfinishWithdraw($user_id, $token_id){
        return
            WithdrawRequest::where('user_id',$user_id)
                ->where('token_id',$token_id)
                ->whereNull('deleted_at')
                ->whereIn('status',[1,2])
                ->first();

    }


    public function createWithdrawRequest($user_id, $token_id, $amount, $address){

        $TokenWalletRepo = new TokenWalletRepo();
        $wallet = $TokenWalletRepo->getWalletWithToken($user_id, 0, $token_id);

        if($wallet && isset($wallet->id) ){

            $amount = TokenTransactionRepo::getTableBalance($amount, $wallet['decimal']);

            if(Erc20TokenRepo::checkBalanceCanBeDecrease($wallet->balance, $amount)){

                $WithdrawRequest = $this->checkUnfinishWithdraw($user_id, $token_id);

                if($WithdrawRequest && empty($WithdrawRequest->id)){
                    return ['error'=>'The last withdraw is not completed'];
                }

            } else {
                return ['error'=>'balance doesn\'t enough'];
            }

        } else {
            return ['error'=>'wallet doesn\'t exist'];
        }

        $request_info = [
                'amount'        => $amount,
                'wallet_id'     => $wallet->id,
                'user_id'       => $user_id,
//                'cost'          => $data['order']->order_total,
                'token_id'      => $wallet->token_id,
//                'order_id'      => $data['order']->order_id,
                'to'            => $address,
                'status'        => 1,
            ];

         return WithdrawRequest::create($request_info);
    }

    public static function cancelWithdraw($order){

        $withdraw = WithdrawRequest::where('id',$order->related_id)->first();

        if(!$withdraw || empty($withdraw->id)){
            return ['error'=>'no request'];
        }

        if($withdraw->status != 1){
            return ['error'=>'Order has been successful, cannot be cancelled'];
        }

        tap($withdraw)->delete();

        return [];
    }

    public function limitWithdraw($wallet, $limit){
        $real_amount = TokenTransactionRepo::getTableBalance($wallet->balance, $wallet->decimal);
        return !Erc20TokenRepo::checkBalanceCanBeDecrease($real_amount, $limit);
    }

    public function getWithdrawList($page, $pagesize){
        $page = $page - 1;
        return
        WithdrawRequest::where('withdraw_request.status','>',1)
            ->select('withdraw_request.*','payment.payment_id','payment.money','payment.currency','payment.is_paid','payment.payment_id','payment.platform')
            ->leftJoin('payment','payment.order_id','=','withdraw_request.order_id')
            ->orderby('id','desc')
            ->offset($page)->limit($pagesize)
            ->get();
    }

    public function getWithdrawListTotal(){
        return WithdrawRequest::where('status','>',1)->count();
    }


}