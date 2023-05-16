<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //= ADMIN =//
    // add product
    public function add_product(Request $request)
    {
    }
    // delete product
    public function delete_product($product_id)
    {
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    //= SUPER_ADMIN =//
    // aprove product
    public function aprove_product(Request $request)
    {
    }
    // reject product
    public function reject_product($product_id)
    {
    }
    // get pending products
    public function get_pending_products()
    {
    }
}
