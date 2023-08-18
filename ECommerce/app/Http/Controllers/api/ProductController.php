<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Color;
use App\Models\PendingProduct;
use App\Models\Product;
use App\Models\Product_image;
use App\Models\Product_tag;
use App\Models\Size;
use App\Models\Tag;
use App\Models\Type;
use App\Models\Variant;
use App\Models\Variant_cart;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class ProductController extends Controller
{
    //= ADMIN =//
    // add product
    public function add_product(Request $request, $category_id)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'description',
            'discount_percentage',
            'product_image.*' => 'required|image|mimes:jpeg,png,gif,bmp,jpg,svg',
            'tag' => 'array',
            'variants' => 'required|array'
        ]);
        $admin = Auth::guard('admin_api')->user();
        $product = new Product();
        $product->admin_id = $admin->id;
        $product->category_id = $category_id;
        $product->name = $request->name;
        $product->price = $request->price;
        if (isset($request->discount_percentage)) {
            $product->discount_percentage = $request->discount_percentage;
        }
        $product->description = $request->description;
        $product->save();
        $pendingProduct = new PendingProduct();
        $pendingProduct->product_id = $product->id;
        $pendingProduct->save();
        // inserting images
        foreach ($request->product_image as $image) {
            $product_image = new Product_image();
            $product_image->product_id = $product->id;
            $product_image->product_image = 'storage/' . $image->store('images', 'public');
            $product_image->save();
        }
        // inserting tags
        if (isset($request->tag)) {
            foreach ($request->tag as $ta) {
                $exists = Tag::where('tag', $ta)->exists();
                if ($exists) {
                    $tag = Tag::where('tag', $ta)->first();
                    $product_tag_exists = Product_tag::where([
                        'product_id' => $product->id,
                        'tag_id' => $tag->id,
                    ])->exists();
                    if ($product_tag_exists) {
                        return response()->json([
                            'status' => 0,
                            'message' => "the product already associated with the tag ($ta). please do not duplicate the tags ",
                        ]);
                    }
                    $product_tag = new Product_tag();
                    $product_tag->product_id = $product->id;
                    $product_tag->tag_id = $tag->id;
                    $product_tag->save();
                }
                if (!$exists) {
                    $tag = new Tag();
                    $tag->tag = $ta;
                    $tag->save();
                    $product_tag = new Product_tag();
                    $product_tag->product_id = $product->id;
                    $product_tag->tag_id = $tag->id;
                    $product_tag->save();
                }
            }
        }
        // inserting variants
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
        // sending response
        $data = Product::with('productImages', 'productTags', 'productVariants')->find($product->id);
        return response()->json([
            'status' => true,
            'product_data' => $data,
        ]);
    }

    public function add_variants(Request $request, $product_id)
    {
        $request->validate([
            'variants' => 'required|array',
        ]);
        $product = Product::where('id', $product_id)->first();
        foreach ($request->variants as $variant1) {
            $v = Variant::where('product_id', $product_id)->where('color_id', $variant1['color_id'])->where('size_id', $variant1['size_id'])->first();
            if (isset($v)) {
                $v->variant_quantity += $variant1['variant_quantity'];
                $product->product_quantity += $variant1['variant_quantity'];
                $product->save();
                $v->save();
            } else {
                $variant = new Variant();
                $variant->product_id = $product_id;
                $variant->color_id = $variant1['color_id'];
                $variant->size_id = $variant1['size_id'];
                $variant->variant_quantity = $variant1['variant_quantity'];
                $product->product_quantity += $variant1['variant_quantity'];
                $product->save();
                $variant->save();
            }
        }
        return response()->json([
            'status' => 1,
            'message' => 'Variants added successfully',
        ]);
    }

    // add tag
    public function add_tag(Request $request, $product_id)
    {
        $request->validate([
            'tag' => 'required',
        ]);
        $exists = Tag::where('tag', $request->tag)->exists();
        if ($exists) {
            $tag = Tag::where('tag', $request->tag)->first();
            $product_tag_exists = Product_tag::where([
                'product_id' => $product_id,
                'tag_id' => $tag->id,
            ])->exists();
            if ($product_tag_exists) {
                return response()->json([
                    'status' => 0,
                    'message' => "the product already associated with the tag ($request->tag).",
                ]);
            }
            $product_tag = new Product_tag();
            $product_tag->product_id = $product_id;
            $product_tag->tag_id = $tag->id;
            $product_tag->save();
            return response()->json([
                'status' => 1,
                'message' => "your product associated with the tag ($request->tag) successfully"
            ]);
        }
        $tag = new Tag();
        $tag->tag = $request->tag;
        $tag->save();
        $product_tag = new Product_tag();
        $product_tag->product_id = $product_id;
        $product_tag->tag_id = $tag->id;
        $product_tag->save();
        return response()->json([
            'status' => 1,
            'message' => 'tag added successfully to your product'
        ]);
    }

    // add discount
    public function add_discount(Request $request, $product_id)
    {
        $request->validate([
            'discount_percentage' => 'required',
        ]);
        $product = Product::where('id', $product_id)->update([
            'discount_percentage' => $request->discount_percentage
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'discount added successfully'
        ]);
    }

    // get all tags
    public function get_all_tags()
    {
        $tags = Tag::get();
        return response()->json([
            'tags' => $tags
        ]);
    }
    public function get_tag_products($tag_id)
    {
        $products_tag = Product_tag::where('tag_id', $tag_id)->with('product.productImages')
            ->whereHas('product', function ($query) {
                $query->where('approved', 1);
            })->get();
        $products =  $products_tag->map(function ($product) {
            return $product->product;
        });
        return response()->json([
            'status' => 1,
            'tag_products' => $products,
        ]);
    }

    // delete product
    public function delete_product($product_id)
    {
        Product::findorFail($product_id)->forceDelete();
        return response()->json([
            'status' => 1,
            'message' => 'Product Deleted Duccessfully'
        ]);
    }

    // product profile
    public function product_profile($product_id)
    {
        $product = Product::with('productImages', 'productTags.tag', 'productVariants.size', 'productVariants.color')->find($product_id);
        return response()->json([
            'product' => $product
        ]);
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    //= SUPER_ADMIN =//
    // aprove product or not
    public function aprove_product(Request $request)
    {
        $pendingProduct = PendingProduct::where('product_id', $request->product_id)->firstOrFail();
        $product = Product::findOrFail($request->product_id);
        if ($request->approved) {
            // approve
            $product->approved = true;
            $product->save();
            $pendingProduct->forceDelete();
            return response()->json([
                'message' => 'Product approved successfully.'
            ]);
        } else {
            // reject
            $pendingProduct->forceDelete();
            $product->forceDelete();
            return response()->json([
                'message' => 'Product rejected successfully.'
            ]);
        }
    }

    // get pending products
    public function get_pending_products()
    {
        $pendingProducts = PendingProduct::with('product.productImages')->get();
        $data = $pendingProducts->map(function ($pd) {
            return [
                'id' => $pd->id,
                'product_id' => $pd->product_id,
                'product' => [
                    'id' => $pd->product->id,
                    'admin_id' => $pd->product->admin_id,
                    'category_id' => $pd->product->category_id,
                    'company_name' => $pd->product->admin->company_name,
                    'category_name' => $pd->product->category->name,
                    'name' => $pd->product->name,
                    'price' => $pd->product->price,
                    'description' => $pd->product->description,
                    'discount_percentage' => $pd->product->discount_percentage,
                    'approved' => $pd->product->approved,
                    'product_quantity' => $pd->product->product_quantity,
                    'sell_count' => $pd->product->sell_count,
                    'product_images' => $pd->product->productImages,
                ],
            ];
        });
        return response()->json([
            'pendingProducts' => $data
        ]);
    }

    // get all products
    public function get_all_products()
    {
        $products = Product::with('productImages')->where('approved', true)->get();
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'admin_id' => $product->admin_id,
                'category_id' => $product->category_id,
                'company_name' => $product->admin->company_name,
                'category_name' => $product->category->name,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'discount_percentage' => $product->discount_percentage,
                'approved' => $product->approved,
                'product_quantity' => $product->product_quantity,
                'sell_count' => $product->sell_count,
                'product_images' => $product->productImages,
            ];
        });
        return response()->json([
            'status' => 1,
            'products' => $data
        ]);
    }

    // get admin products
    public function get_admin_products($admin_id)
    {
        $products = Product::where('admin_id', $admin_id)->where('approved', true)->with('productImages')->get();
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'admin_id' => $product->admin_id,
                'category_id' => $product->category_id,
                'company_name' => $product->admin->company_name,
                'category_name' => $product->category->name,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'discount_percentage' => $product->discount_percentage,
                'approved' => $product->approved,
                'product_quantity' => $product->product_quantity,
                'sell_count' => $product->sell_count,
                'product_images' => $product->productImages,
            ];
        });
        return response()->json([
            'status' => 1,
            'products' => $data
        ]);
    }

    // add color
    public function add_color(Request $request)
    {
        $request->validate([
            'color' => 'required',
            'hex' => 'required',
        ]);
        $c = Color::where('hex', $request->hex)->first();
        if (isset($c)) {
            return response()->json([
                'status' => 0,
                'message' => 'Color already exists',
                'color' => $c
            ]);
        }
        $color = new Color();
        $color->color = $request->color;
        $color->hex = $request->hex;
        $color->save();
        return response()->json([
            'status' => 1,
            'message' => 'Color added successfully'
        ]);
    }
    // get colors
    public function get_colors()
    {
        $colors = Color::all();
        return response()->json([
            'status' => 1,
            'colors' => $colors
        ]);
    }
    // add type
    public function add_type(Request $request)
    {
        $request->validate([
            'type' => 'required',
        ]);
        $t = Type::where('type', $request->type)->first();
        if (isset($t)) {
            return response()->json([
                'status' => 0,
                'message' => 'type already exists',
                'type' => $t
            ]);
        }
        $type = new Type();
        $type->type = $request->type;
        $type->save();
        return response()->json([
            'status' => 1,
            'message' => 'type added successfully',
            'type' => $type
        ]);
    }
    // add size
    public function add_size(Request $request, $type_id)
    {
        $request->validate([
            'size' => 'required',
        ]);
        $s = Size::where('size', $request->size)->first();
        if (isset($s)) {
            return response()->json([
                'status' => 0,
                'message' => 'size already exists',
                'size' => $s
            ]);
        }
        $size = new Size();
        $size->size = $request->size;
        $size->type_id = $type_id;
        $size->save();
        return response()->json([
            'status' => 1,
            'message' => 'size added successfully',
            'size' => $size
        ]);
    }
    // get types
    public function get_types()
    {
        $types = Type::all();
        return response()->json([
            'status' => 1,
            'types' => $types
        ]);
    }
    // get sizes
    public function get_type_sizes($type_id)
    {
        $sizes = Size::where('type_id', $type_id)->get();
        return response()->json([
            'status' => 1,
            'sizes' => $sizes
        ]);
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //= USER =//
    // add_to_cart
    public function add_to_cart(Request $request, $variant_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $variant = Variant::where('id', $variant_id)->first();
        if ($variant->variant_quantity >= $request->quantity) {
            $user = Auth::guard('user_api')->user();
            $cart = Cart::where('user_id', $user->id)->first();
            $exists = Variant_cart::where('variant_id', $variant->id)->where('cart_id', $cart->id)->exists();
            if ($exists) {
                $v = Variant_cart::where('variant_id', $variant->id)->where('cart_id', $cart->id)->first();
                if (($v->quantity + $request->quantity) < $variant->variant_quantity) {
                    Variant_cart::where('variant_id', $variant->id)->where('cart_id', $cart->id)->update([
                        'quantity' => $v->quantity + $request->quantity,
                    ]);
                } else {
                    return response()->json([
                        'status' => 0,
                        'message' => 'the variants is not enough for your request'
                    ]);
                }
            } else {
                $variant_cart = new Variant_cart();
                $variant_cart->variant_id = $variant_id;
                $variant_cart->cart_id = $cart->id;
                $variant_cart->quantity = $request->quantity;
                $variant_cart->save();
            }
            return response()->json([
                'status' => 1,
                'message' => 'variant added to cart successfully'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'the product quantity is not enough'
            ]);
        }
    }

    // increase quantity
    public function increase_quantity($variant_id)
    {
        $user = Auth::guard('user_api')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        $variant_cart = Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->first();
        $variant = Variant::where('id', $variant_id)->first();
        if (($variant_cart->quantity + 1) < $variant->variant_quantity) {
            Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->update([
                'quantity' => $variant_cart->quantity + 1
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'incresed successfully'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'there is no more variants to add'
            ]);
        }
    }

    // decrease quantity
    public function decrease_quantity($variant_id)
    {
        $user = Auth::guard('user_api')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        $variant_cart = Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->first();
        if (($variant_cart->quantity - 1) == 0) {
            Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->forceDelete();
            return response()->json([
                'status' => 0,
                'message' => 'variant deleted successfully'
            ]);
        } else {
            Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->update([
                'quantity' => $variant_cart->quantity - 1
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'decresed successfully'
            ]);
        }
    }

    // remove_from_cart
    public function remove_from_cart($variant_id)
    {
        $user = Auth::guard('user_api')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        Variant_cart::where('cart_id', $cart->id)->where('variant_id', $variant_id)->forceDelete();
        return response()->json([
            'status' => 1,
            'message' => 'variant removed from cart successfully'
        ]);
    }

    // clear cart
    public function clear_cart()
    {
        $user = Auth::guard('user_api')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        Variant_cart::where('cart_id', $cart->id)->forceDelete();
        return response()->json([
            'status' => 1,
            'message' => 'cart cleared successfully'
        ]);
    }

    // get_cart_items
    public function get_cart_items()
    {
        $user = Auth::guard('user_api')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        // $items = Variant_cart::where('cart_id', $cart->id)->get();
        $variantCarts = Variant_cart::where('cart_id', $cart->id)->with('variant.product.productImages', 'variant.color', 'variant.size')->get();
        $items = $variantCarts->map(function ($vc) {
            return [
                'variant_id' => $vc->variant->id,
                'product' => $vc->variant->product,
                'size' => $vc->variant->size,
                'color' => $vc->variant->color,
                'quantity' => $vc->quantity,
            ];
        });
        return response()->json([
            'status' => 1,
            'cart_items' => $items
        ]);
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    // SEARCH
    // search in all products
    public function search_all_products(Request $request)
    {
        $tag = Tag::where('tag', $request->serched_product)->first();
        if (isset($tag)) {
            $productTags = Product_tag::where('tag_id', $tag->id)->get();
            $data = [];
            foreach ($productTags as $item) {
                $product = Product::where('id', $item->product_id)->first();
                if ($product->approved == 1) {
                    $data[] = Product::with('productImages')->find($product->id);
                }
            }
            return response()->json([
                'status' => 1,
                'is_tag' => true,
                'data' => $data
            ]);
        }
        if ($request->serched_product !== null) {
            if (Product::where('name', 'LIKE', '%' . $request->serched_product . '%')->where('approved', 1)->exists()) {
                $products = Product::where('name', 'LIKE', '%' . $request->serched_product . '%')->where('approved', 1)->get(['id']);
                $data = [];
                foreach ($products as $item) {
                    $data[] = Product::with('productImages')->find($item->id);
                }
                return response()->json([
                    'status' => 1,
                    'is_tag' => false,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Not Found',
                    'data' => []
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Enter Somthing To Search',
                'data' => []
            ]);
        }
    }
    // search in admin products
    public function search_admin_products(Request $request, $admin_id)
    {
        if ($request->serched_product !== null) {
            if (Product::where('admin_id', $admin_id)->where('name', 'LIKE', '%' . $request->serched_product . '%')->where('approved', 1)->exists()) {
                $products = Product::where('admin_id', $admin_id)->where('name', 'LIKE', '%' . $request->serched_product . '%')->where('approved', 1)->get(['id']);
                $data = [];
                foreach ($products as $item) {
                    $data[] = Product::with('productImages')->find($item->id);
                }
                return response()->json([
                    'status' => 1,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Not Found',
                    'data' => []
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Enter Somthing To Search',
                'data' => []
            ]);
        }
    }
}
