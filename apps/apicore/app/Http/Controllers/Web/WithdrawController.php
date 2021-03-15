<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Utils\Transformer;
use App\Utils\Constants;

use App\Repositories\Erc20TokenRepo;
use App\Repositories\OrdersRepo;
use App\Repositories\ProductRepo;
use App\Repositories\TokenTransactionRepo;
use App\Repositories\TokenWalletRepo;
use App\Repositories\WithdrawRepo;
use App\Models\WithdrawRequest;
use App\Models\Erc20Token;

use DB, Queue;

class WithdrawController extends Controller
{
    private $_transformer;

    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer )
    {
        $this->_transformer = $transformer;
    }


    public function userGetWithdrawInfo(Request $request,
                                        ProductRepo $productRepo,
                                        TokenWalletRepo $tokenWalletRepo,
                                        WithdrawRepo $withdrawRepo){
        $user = $request->user();

        if(empty($user->id)){
            return $this->_transformer->fail('40038','please login');
        }

        $wallet = $tokenWalletRepo->getByTokenWalletId($request->input('wallet_id', 0));

        if(!$wallet || empty($wallet->token_id)){
            return $this->_transformer->fail('40042','wallet missing');
        }

//        $limit = 1;
//        $is_limit = $withdrawRepo->limitWithdraw($wallet, $limit);
//
//        if($is_limit){
//            return $this->_transformer->fail('40040',"Wallet balance needs to be higher than $limit to withdraw");
//        }

        $product = $productRepo->getWithdrawProduct($wallet->token_id);

        if(empty($product) || empty($product->id)){
            return $this->_transformer->fail('40041','product disabled');
        }

        $withdraw = $withdrawRepo->checkUnfinishWithdraw($user->id, $wallet->token_id);
//        if($withdraw && isset($withdraw->id)){
//            return $this->_transformer->fail('40039','Under review');
//        }

        if(!$withdraw || empty($withdraw->id)){
            $withdraw = new \stdClass();
        } else {
            $withdraw->amount = TokenTransactionRepo::getRealBalance($withdraw->amount, $wallet->decimal);
        }

        $token = erc20token::where('id',$wallet->token_id)
                ->select('contract_address')
                ->first();

        return $this->_transformer->success([
            'product'=>$product,
            'withdraw'=>$withdraw,
            'token'=>
            [
                'address'=>$token->contract_address,
                'contract_url'=> Constants::get_etherscan_link() . '/address/' . $token->contract_address,
            ]
        ]);

    }

    public function createWithdrawRequest(Request $request,
                                            OrdersRepo $ordersRepo,
                                            ProductRepo $productRepo,
                                            TokenWalletRepo $tokenWalletRepo,
                                            WithdrawRepo $withdrawRepo){

        $address = $request->input('address');
        $amount = $request->input('amount',0);

        $user   = $request->user();

        if($amount<=0){
            return $this->_transformer->fail('40062','missing amount');
        }

        if(empty($user->id)){
            return $this->_transformer->fail('40052','missing buyer');
        }

        if(!$address){
            return $this->_transformer->fail('40051','missing address');
        }
        \Log::info($request->input('wallet_id', 0));

        $wallet = $tokenWalletRepo->getByTokenWalletId($request->input('wallet_id', 0));

        if(empty($wallet) || empty($wallet->id)){
            return $this->_transformer->fail('40059', 'missing wallet');
        }

        if($wallet->user_id != $user->id){
            return $this->_transformer->fail('40060', 'missing wallet');
        }
//        check_decimal_part_error

        //check unfinish withdraw
        $withdraw = $withdrawRepo->checkUnfinishWithdraw($user->id, $wallet->token_id);
        if($withdraw && isset($withdraw->id)){
            return $this->_transformer->fail('40061','another request has been created');
        }

        //create withdraw
        $withdraw = $withdrawRepo->createWithdrawRequest($user->id, $wallet->token_id, $amount, $address);

        if(!$withdraw){
            return $this->_transformer->fail('40058','error');
        }

        if(!empty($withdraw['error'])){
            return $this->_transformer->fail('40053',$withdraw['error']);
        }

        $product = $productRepo->getWithdrawProduct($wallet->token_id);

        if(empty($product) || empty($product->id)){
            return $this->_transformer->fail('40041','product disabled');
        }

        $order = $ordersRepo->createOrderByProduct($user->id,  0, $product->id, $request->server('HTTP_USER_AGENT'), $withdraw->id );

        if(empty($order->id) || !empty($order['error'])){
            tap($withdraw)->delete();
            return $this->_transformer->fail('40054',empty($order['error']) ? 'error' : $order['error'] );
        }

        tap($withdraw)->update([
            'order_id' => $order->order_id
        ]);

        $withdraw->amount = $amount;

        return $this->_transformer->success(['withdraw'=>$withdraw]);
    }


    // for check
    public function getWithdrawRequestList(Request $request,
                                            WithdrawRepo $withdrawRepo
    ){

        $page = $request->input('pageNumber',0);
        $pagesize = 20;

        if($page){
            $list = $withdrawRepo->getWithdrawList($page, $pagesize);
            return $this->_transformer->success(['list'=>$list]);
        }

        $totalnumber = $withdrawRepo->getWithdrawListTotal();;

        if($request->input('key','') != '123321'){
            return 'error';
        }

        return view('withdraw.list', [
            'pagesize' => $pagesize,
            'totalnumber' => $totalnumber,
            'basepath' => base_path(),
        ]);
    }

    public function withdrawTransafer(Request $request){
        $id = $request->input('id',0);

        $withdraw = WithdrawRequest::where('withdraw_request.id',$id)
            ->select('withdraw_request.status','payment.is_paid')
            ->leftjoin('payment','payment.order_id','=','withdraw_request.order_id')
            ->first();

        if($withdraw->status == 2 && $withdraw->is_paid == 1 ){
            WithdrawRequest::where('id',$id)->update(['status'=>3]);
        }

        return $this->_transformer->success(['transafer'=>1]);

    }

}