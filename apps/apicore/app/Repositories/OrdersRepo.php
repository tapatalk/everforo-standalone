<?php

namespace App\Repositories;

use App\Models\Orders;
use App\Models\Product;
use App\Utils\Constants;

class OrdersRepo
{

    public function __construct()
    {

    }

    public static function channel_to_prefix($channel = 0){
        if($channel == Constants::CHANNEL_ID_AIRDROP){  //AIR DROP;
            return 'ADP';
        }

        if($channel == Constants::CHANNEL_ID_ORDER){  //order
            return 'O';
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

    /**
     * todo, do not pass \Illuminate\Http\Request
     * @param Request $request
     * @return string
     */
    public static function getIpFromRequest(\Illuminate\Http\Request $request)
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $list = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($list as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (explode(',', $_SERVER[$key]) as $ip)
                {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                    {
                        return $ip;
                    }
                }
            }
        }

        return $request->getClientIp();
    }


    public function createOrderByProduct($buyer_id = 0, $group_id = 0, $product_id, $user_agent, $related_id, $extra_info= []){

        $product = Product::find($product_id);

        if(!$product || empty($product->id) || empty($product->price) || empty($product->currency) ){
            return  ['error'=>'product doesn\'t exist'];
        }

        if($product->status  == 0 ){
            return  ['error'=>'product doesn\'t sell'];
        }

        $order = self::createOrder($user_agent, $buyer_id , $group_id, $product->id , $product->price, $product->currency, '', $related_id,$extra_info );

        if(empty($order->id)){
            return  ['error'=>'init order failed'];
        }

        return $order;

    }


    public static function createOrder($user_agent, $user_id = 0 , $group_id = 0, $product_id , $order_total, $currency , $token = '' , $related_id, $extra_info = [])
    {

        $info = [
            'user_id' => $user_id,
            'order_id' => self::createTransactionId('P'),
            'product_id' => $product_id,
            'group_id' => $group_id,
            'order_total' => $order_total,
            'currency' => $currency,
            'token' => $token,
            'user_agent' => $user_agent,
            'related_id' => $related_id,
            'status' => 1,
        ];

        if(!empty($extra_info)){
            $info['extra_info'] = is_array($extra_info) ? json_encode($extra_info) : $extra_info;
        }

        return Orders::create($info);

    }

    public static function getOrderByToken($token){
        return Orders::where('token',$token)->first();
    }

    public static function getOrderByOrderId($order_id){
        return Orders::where('order_id',$order_id)->first();
    }

    public static function getOrderStatus($order){
        switch($order->status){
            case 1:
                return 'created';
                break;
            case 2:
                return 'paid';
                break;
        }
    }

    public static function getUnfinishedGroupCreateTokenOrder($token_id) {
        return Orders::where('related_id',$token_id)
            ->where('product_id',Constants::PRODUCT_ID_CREATE_TOKEN)
            ->where('status',Constants::ORDER_CREATED)
            ->first();
    }

    public static function cancelProduct($order){

        if(self::getOrderStatus($order) != 'created'){
            return false;
        }

        switch($order->product_id){
            case Constants::PRODUCT_ID_CREATE_TOKEN:
                $result = Erc20TokenRepo::cancelCreateToken($order);
                break;
            case Constants::PRODUCT_ID_WITHDRAW_TOKEN:
                $result = WithdrawRepo::cancelWithdraw($order);
                break;
        }

        return empty($result['error']) ? true : false;
    }



}