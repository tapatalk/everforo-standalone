<?php

namespace App\Http\Controllers\Web;

use App\Repositories\OrdersRepo;
use App\Repositories\ProductRepo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Utils\Constants;
use App\Models\Product;
use DB, Queue;

class ProductController extends Controller
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

    public function getPrice($product_name, ProductRepo $ProductRepo){

        $product = $ProductRepo->getProductByname($product_name);

        if($product){
            return $this->_transformer->success($product);
        } else {
            return $this->_transformer->fail('40081','disable');
        }
    }

}