<?php

namespace App\Repositories;


use App\Models\Product;
use App\Utils\Constants;

class ProductRepo
{

    public function __construct()
    {

    }

    public function getProduct($product_id){
        return Product::find($product_id);
    }


    public function getWithdrawProduct($token_id) {

        return Product::where('product_type',Constants::PRODUCT_ID_WITHDRAW_TOKEN)
            ->where('related_id',$token_id)->first();

    }

    public function getProductByname($name, $product_type){

        return Product::where('product_name',$name)->first();

    }

}