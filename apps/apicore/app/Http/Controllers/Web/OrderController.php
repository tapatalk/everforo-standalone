<?php

namespace App\Http\Controllers\Web;

use App\Repositories\OrdersRepo;
use App\Repositories\Erc20TokenRepo;
use App\Repositories\WithdrawRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Utils\Constants;
use DB, Queue;

class OrderController extends Controller
{

    private $_transformer;

    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer)
    {
        $this->_transformer = $transformer;
    }


    public function cancelOrder(Request $request){

        $order_id = $request->input('order_id');

        $order = OrdersRepo::getOrderByOrderId($order_id);

        if(!$order || empty($order->id)){
            return $this->_transformer->fail('40055','cancel failed');
        }

        if (OrdersRepo::cancelProduct($order)) {
            tap($order)->delete();

            return $this->_transformer->success();
        }

        return $this->_transformer->fail('40056','cancel failed');
    }

}