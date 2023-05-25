<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\Product_image;
use App\Models\Product_tag;
use App\Models\Size;
use App\Models\Tag;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //= ADMIN =//
    // add product
    public function add_product(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'price' => 'required',
                'description',
                'product_image.*' => 'required|image|mimes:jpeg,png,gif,bmp,jpg,svg',
                // 'tag' => 'unique:tags',
                'variants' => 'required|array'
            ],
            // ['tag.unique' => 'the tag is already exists',]
        );
        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();
        foreach ($request->product_image as $image) {
            $product_image = new Product_image();
            $product_image->product_id = $product->id;
            $product_image->product_image = 'storage/' . $image->store('images', 'public');
            $product_image->save();
        }
        // if (isset($request->tag)) {
        //     foreach ($request->tag as $ta) {
        //         $tag = new Tag();
        //         $tag->tag = $ta;
        //         $tag->save();
        //         $product_tag = new Product_tag();
        //         $product_tag->product_id = $product->id;
        //         $product_tag->tag_id = $tag->id;
        //         $product_tag->save();
        //     }
        // }
        foreach ($request->variants as $variant1) {
            $variant = new Variant();
            $variant->product_id = $product->id;
            $variant->color_id = $variant1['color_id'];
            $variant->size_id = $variant1['size_id'];
            $variant->variant_quantity = $variant1['variant_quantity'];
            $product->product_quantity += $variant1['variant_quantity'];
            $product->save();
            $variant->save();
        }
        return response()->json([
            'status' => true,
            'product_data' => $product,
        ]);
    }

    // delete product
    public function delete_product($product_id)
    {
        Product::findorFail($product_id)->forceDelete();
        return response()->json([
            'status' => true,
            'message' => 'Product Deleted Duccessfully'
        ]);
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    //= SUPER_ADMIN =//
    // aprove product
    public function aprove_product(Request $request)
    {
        // $pendingProduct = PendingProduct::where('product_id', $request->product_id)->firstOrFail();
        // $product = Product::findOrFail($request->product_id);

        // if ($request->approve) {
        //     $product->approved = true;
        //     $product->save();
        //     $pendingProduct->delete();
        //     return response()->json(['message' => 'Product approved successfully.']);
        // } else {
        //     $pendingProduct->delete();
        //     $product->delete();
        //     return response()->json(['message' => 'Product rejected successfully.']);
        // }
    }
    // reject product
    public function reject_product($product_id)
    {
    }
    // get pending products
    public function get_pending_products()
    {
    }

    // get colors
    public function get_colors()
    {
        $colors = Color::all();
        return response()->json([
            'status' => true,
            'colors' => $colors
        ]);
    }
    // get sizes
    public function get_sizes()
    {
        $sizes = Size::all();
        return response()->json([
            'status' => true,
            'sizes' => $sizes
        ]);
    }
}
