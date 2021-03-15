<?php

namespace App\Http\Controllers\Web;


use App\Http\Controllers\API\Auth\LoginController;
use App\Repositories\PaypalRepo;
use App\Repositories\OrdersRepo;
use App\Repositories\Erc20TokenRepo;
use App\Repositories\PaypalWebHookRepo;
use App\Repositories\WithdrawRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Repositories\TokenTransactionRepo;
use App\Utils\Constants;
use App\WriteLog;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Product;
use App\Models\WithdrawRequest;
use App\Repositories\TokenWalletRepo;
use DB, Queue;

class PaypalController extends Controller
{

    private $_transformer;
    private $_group;
    private $payPalRepo;
    private $_buyer;

    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer, PaypalRepo $PaypalRepo)
    {
        $this->_transformer = $transformer;
        $this->payPalRepo = $PaypalRepo;
    }

    public function getTokenByOrder(Request $request){

        // $group_id = $request->input('group_id',0);
        $order_id = $request->input('order_id');

        if(!$order_id){
            return $this->_transformer->fail('40070','occur error');
        }

        $order = OrdersRepo::getOrderByOrderId($order_id);

        $this->_buyer = $request->user();

        $token = $this->paypalCharge($request, $order);

        if($token){
            tap($order)->update(['token'=>$token]);
        }

        return $this->_transformer->success(['token' =>$token]);

    }

    // //paypal buy create token
    // public function getTokenFromBuyCreateToken(Request $request){

    //     $this->_group = config('app.group');
    //     $this->_buyer = $request->user();

    //     $data = $this->getToken($request, Constants::PRODUCT_ID_CREATE_TOKEN);
    //     if(isset($data['token'])){
    //         return $this->_transformer->success(['token' => $data['token']]);
    //     }

    //     if($data instanceof Transformer ){
    //         return $data;
    //     }

    //     return $this->_transformer->fail('40034','occur error');
    // }



    //paypal buy token withdraw
    public function getTokenFromWithdraw(Request $request){
        $this->_group = false;
        $this->_buyer = $request->user();
//        $amount = $request->input('amount');
        $token_id = $request->input('token_id');
//        $to = $request->input('to');

        if(!isset($this->_buyer->id)){
            return $this->_transformer->fail('40032','buyer doesn\'t exist');
        }


        $data = $this->getToken($request, Constants::PRODUCT_ID_WITHDRAW_TOKEN);
        if(isset($data['token'])){
            return $this->_transformer->success(['token' => $data['token']]);
        }

        if($data instanceof Transformer ){
            return $data;
        }

        return $this->_transformer->fail('40034','occur error');



//        if(!$to){
//            return $this->_transformer->fail('40033','missing address');
//        }

//        $WithdrawRepo = new WithdrawRepo();
//        $withdraw = $WithdrawRepo->checkWithdraw($amount, $this->_buyer->id, $token_id);
//
//        if(isset($withdraw['wallet'])){
//            $data = $this->getToken($request, Constants::PRODUCT_ID_WITHDRAW_TOKEN);
//            if($data instanceof Transformer ){
//                return $data;
//            }
//            if(isset($data['token'])){
//                $request_info = [
//                    'amount'        => TokenTransactionRepo::getTableBalance($amount,$withdraw['wallet']->decimal),
//                    'wallet_id'     => $withdraw['wallet']->id,
//                    'user_id'       => $this->_buyer->id,
//                    'cost'          => $data['order']->order_total,
//                    'token_id'      => $withdraw['wallet']->token_id,
//                    'order_id'      => $data['order']->order_id,
//                    'to'            => $to,
//                    'status'        => 1,
//                ];
//                WithdrawRequest::create($request_info);
//                return $this->_transformer->success(['token' => $data['token']]);
//            }
//
//        }
//
//        if(isset($withdraw['error'])){
//            return $this->_transformer->fail('40033',$withdraw['error']);
//        }
//
//        if(isset($withdraw['withdraw'])){
//            return $this->_transformer->success(['withdraw' => $withdraw['withdraw']]);
//        }

        return $this->_transformer->fail('40036', 'occur error');
    }


    public function getToken(Request $request, $product_id ,$extra_info = [] ){

        $product = Product::find($product_id);

        if(!$product || empty($product->id) || empty($product->price) || empty($product->currency) ){
            return $this->_transformer->fail('40023','product doesn\'t exist');
        }

        if($product->status  == 0 ){
            return $this->_transformer->fail('40024','product doesn\'t sell');
        }


        if(!isset($this->_buyer->id)){
            return $this->_transformer->fail('40025','buyer doesn\'t exist');
        }

        $group_id = !empty($this->_group->id) ? $this->_group->id : 0;

        $order = OrdersRepo::createOrder($request->server('HTTP_USER_AGENT'), $this->_buyer->id , $group_id, $product->id , $product->price, $product->currency, '', $extra_info );

        if(empty($order->id)){
            return $this->_transformer->fail('40026','init order failed');
        }

        $token = $this->paypalCharge($request, $order);

        if(!$token){
            tap($order)->update(['status'=>4]);
            return $this->_transformer->fail('40027','init token failed');
        }

        tap($order)->update(['token'=>$token]);

//        return $this->_transformer->success(['token' => $token]);
        return ['token' => $token, 'order'=>$order];
    }

    /**
     * @param Request $request
     * @param BuyerDataObj $buyerD
     * @param bool $webPay
     * @return string
     * @throws Exception
     */
    public function paypalCharge(Request $request, $order)
    {
        $this->payPalRepo->setApiContext();


//        try {
//            $amount = $this->getAmount($request,'paypal');

//            $metaData = $this->getMetaData($request, $buyerD);
            $metaData = [];

            $returnUrl = url('/paypal/web_execute') . '?type=donate';
            $cancelUrl = url('/paypal/rechargeCancel');

            $amount = $order->order_total;
            $metaData['order_id'] = $order->order_id;


            $token = $this->payPalRepo->doPayPalCharge($amount, $metaData, $returnUrl, $cancelUrl);

            if (!$token) {
                throw new \Exception('can\'t get valid payment token');
            }
            $client_ip = \App\Repositories\OrdersRepo::getIpFromRequest($request);
            $userAgent = $request->server('HTTP_USER_AGENT');
//            $this->buyGoodsRepo->logs("payPalCharge OneTimeDonate creat-token:{$token} fid:{$fid} money:{$money} order_id:{$metaData['order_id']} forum_user_id:{$buyerD->getUserId()} forum_username:{$buyerD->getUsername()} ip:{$client_ip} userAgent:{$userAgent}");
            return $token;

//        } catch (\Exception $e) {
//            throw new \Exception($e->getMessage(), $e->getCode());
//        }
    }


    public function payPalChargeExecute(Request $request)
    {
        $response = [];

        try
        {
            $payment_id = $request->input("paymentID");
            $payer_id = $request->input("payerID");

            if (!$payment_id || !$payer_id)
            {
                throw new \Exception('payment_id or payer_id missing ' . $payer_id . ' ' . $payment_id, 499);
            }


            $payment = $this->payPalRepo->confirmPayPalCharge($payment_id, $payer_id);

            if (!$payment)
            {
                throw new \Exception('can\'t get valid execute result');
            }

            $response['state'] = $payment->getState();
            $response['id']    = $payment->getId();

            $transactions = $payment->getTransactions();

            $customData = $transactions[0]->getCustom();

            $customData = unserialize(base64_decode($customData));

            $order_id = empty($customData['order_id']) ? '' : $customData['order_id'];

            $order = Orders::where('order_id',$order_id)->first();

            $paymentinfo = [
                'payment_id'    => $payment_id,
                'platform'      => 'paypal'
            ];

            $amount = $transactions[0]->getAmount();
            if(!empty($amount->total)){
                $paymentinfo['money']  = $amount->total;
            }
            if(!empty($amount->currency)){
                $paymentinfo['currency']  = $amount->currency;
            }

            if($order && !empty($order->id) ){
                $paymentinfo['order_id']  = $order->order_id;
            }

            $payment = Payment::create($paymentinfo);

            if($response['state'] == 'approved' ){

                $paymentinfo['is_paid']  = 1;
                \Log::error('paid paypal');
                tap($order)->update(['status'=>2]);

                $response =  array_merge($this->deliverProduct($order),$response);
            }

            tap($payment)->update($paymentinfo);

        } catch (\Exception $e)
        {
            return  $this->_transformer->fail(40022,$e->getMessage());
        }

        return $this->_transformer->success($response);
    }

    public function deliverProduct($order){

        if($order->product_id == Constants::PRODUCT_ID_CREATE_TOKEN){
            // create token
            tap($order)->update(['status'=>5]);
            $Erc20TokenRepo = new Erc20TokenRepo();
            $token = $Erc20TokenRepo->createERC20TokenAfterPaypalconfirm($order);

            \Log::error('create token deliverProduct');
            return ['token'=>$token];
        }

        if($order->product_id == Constants::PRODUCT_ID_WITHDRAW_TOKEN){

            tap($order)->update(['status'=>5]);

            $withdraw = WithdrawRequest::where('order_id',$order->order_id)->first();
            //decrease wallet amount

            $TokenWalletRepo = new TokenWalletRepo();
            $wallet = $TokenWalletRepo->getWalletWithToken($withdraw->user_id, 0, $withdraw->token_id);

            if(!$wallet || !isset($wallet->id)){
                Log::error('missing wallet ' );
                return false;
            }

            $TokenTransactionRepo = new TokenTransactionRepo();
            $transaction = $TokenTransactionRepo->transactionToken( $wallet->id, 0, $withdraw->token_id, $withdraw->amount, Constants::CHANNEL_ID_WITHDRAW);

            if(isset($transaction['error'])){
                tap($withdraw)->update(['status'=>5]);
                return ['error'=>$transaction['error']];
            }

            if(!empty($transaction['decrease'])){
                tap($withdraw)->update(['status'=>2]);
            }

            \Log::error('withdraw deliverProduct');
            return ['withdraw'=>$withdraw];
        }

        return [];
    }

    public function webhook( ){

        $PaypalWebHookRepo = new PaypalWebHookRepo();
        $body = $PaypalWebHookRepo->webHook();
        WriteLog::paywebhook('web hook start');
        switch ($body->event_type)
        {
//                case 'BILLING.PLAN.CREATED':
//                    break;
//                case 'BILLING.PLAN.UPDATED':
//                    break;
//                case 'BILLING.SUBSCRIPTION.RE-ACTIVATED':
//                    break;
//                case 'BILLING.SUBSCRIPTION.SUSPENDED':
//                    break;
//                case 'BILLING.SUBSCRIPTION.UPDATED':
//                    break;
            case 'BILLING.SUBSCRIPTION.CREATED':
//                    $this->billingSubscriptionCreated($this->bodyObject->resource);
                break;
            case 'PAYMENT.SALE.COMPLETED':
                \Log::error('success:' . json_encode($body->resource));
                $this->paymentSaleCompleted($body->resource);
                break;
            case 'BILLING.SUBSCRIPTION.CANCELLED':
//                    $this->billingSubscriptionCancelled($this->bodyObject->resource);
                break;
            case 'PAYMENT.SALE.REFUNDED':
//                    $this->paymentSaleRefund($this->bodyObject->resource);
                break;
            default:
//                    $this->PayPal->payPalLog('[NOTICE] not tracked event:' . $this->bodyObject->event_type . ' web hook id:' . $this->bodyObject->id);
        }

    }

    private function paymentSaleCompleted($resource){
        $customData = $resource->custom;
        $customData = unserialize(base64_decode($customData));
        $order_id = empty($customData['order_id']) ? '' : $customData['order_id'];
        $order = Orders::where('order_id',$order_id)->first();

        if(!$order || empty($order->id)){
            WriteLog::paywebhookerror('web hook: no order id match',(array)$resource);
            exit();
        }
        WriteLog::paywebhook('info: ',(array)$resource);


        if($order->status != 5 ){
            if($order->status == 1 || $order->status == 2 ){
                $this->deliverProduct($order);
            }
            if($order->status == 3 || $order->status == 4){
                WriteLog::paywebhookerror('wait for Manual confirmation PAY:ID: ',$resource->parent_payment);
            }

            $payment = Payment::where('order_id',$order->order_id)->first();
            if(!$payment){

                $paymentinfo = [
                    'payment_id'    => $resource->parent_payment,
                    'platform'      => 'paypal',
                    'is_paid'       => 1
                ];
                $amount = $resource->amount;
                if(!empty($amount->total)){
                    $paymentinfo['money']  = $amount->total;
                }
                if(!empty($amount->currency)){
                    $paymentinfo['currency']  = $amount->currency;
                }

                $paymentinfo['order_id']  = $order->order_id;

                Payment::create($paymentinfo);
            }
        }
        exit();

    }






}