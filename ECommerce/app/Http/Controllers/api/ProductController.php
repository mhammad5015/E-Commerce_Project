<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Product_image;
use App\Models\Product_tag;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //= ADMIN =//
    // add product
    public function add_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            // 'description',
            //
            'product_image' => 'required|image|mimes:jpeg,png,gif,bmp,jpg,svg',
            //
            'tag',
            //
            'color_id' => 'required',
            'size_id' => 'required',
            'variant_quantity' => 'required',
        ]);
        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->product_quantity = $product->product_quantity + $request->variant_quantity;
        $product->save();

        $product_image = new Product_image();
        $product_image->product_id = $product->id;
        $product_image->product_image = 'storage/' . $request->file('product_image')->store('images', 'public');
        $product_image->save();

        if (isset($request->tag)) {
            $tag = new Product_tag();
            $tag->tag = $request->tag;
            $tag->save();
        }

        $variant = new Variant();
        $variant->product_id = $product->id;
        $variant->color_id = $request->color_id;
        $variant->size_id = $request->size_id;
        $variant->variant_quantity = $request->variant_quantity;
        $variant->save();

        return response()->json([
            'status' => true,
            'product_data' => $product,
            'product_image' => $product_image,
            'variant' => $variant,
        ]);
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
