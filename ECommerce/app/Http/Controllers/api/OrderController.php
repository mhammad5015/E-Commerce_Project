<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\Variant_cart;
use App\Models\Variant;
use App\Models\Product;
use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\Color;
use App\Models\Size;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function confirm_order()
    {
        $user = Auth::guard('user_api')->user();
        $user_cart = Cart::where('user_id', $user->id)->first();

        // Get the variant cart items
        $variantCarts = Variant_cart::where('cart_id', $user_cart->id)->get();
        if ($variantCarts->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'The cart is empty , please check your cart ',
            ]);
        }
        $super_admin = SuperAdmin::first();
        // Create a new order record
        $order = new Order();
        $order->user_id = $user->id;
        $order->save();

        $unavailable_items = [];
        foreach ($variantCarts as $variantCart) {
            // Get the variant
            $variant = Variant::find($variantCart->variant_id);
            $product = Product::find($variant->product_id);
            $admin = Admin::find($product->admin_id);
            // Check if variant quantity is sufficient
            if ($variantCart->quantity <= $variant->variant_quantity) {
                // Create order item
                $orderItem = new Order_items;
                $orderItem->order_id = $order->id;
                $orderItem->variant_id = $variantCart->variant_id;
                $orderItem->quantity = $variantCart->quantity;
                if ($product->discount_percentage == 0) {
                    $orderItem->price = ($product->price) * ($orderItem->quantity);
                    $order->Total_Price += $orderItem->price;
                    $order->save();
                    $super_admin->wallet += ($orderItem->price) * ($admin->percentage);
                    $super_admin->save();
                    $admin->wallet += ($orderItem->price) - ($super_admin->wallet);
                    $admin->save();
                } else {
                    $new_Price = ($product->price) - ($product->price) * ($product->discount_percentage) / 100;
                    $orderItem->price = ($new_Price) * ($orderItem->quantity);
                    $order->Total_Price += $orderItem->price;
                    $order->save();
                    $super_admin->wallet += ($orderItem->new_Price) * ($admin->percentage);
                    $super_admin->save();
                    $admin->wallet += ($orderItem->new_Price) - ($super_admin->wallet);
                    $admin->save();
                }
                $orderItem->save();
                // Decrement variant quantity
                $variant->variant_quantity -= $variantCart->quantity;
                $product->product_quantity -= $variantCart->quantity;
                $product->sell_count += $variantCart->quantity;
                $variant->save();
                $product->save();
            } else {
                $unavailable_items[] = $product->name;
            }
        }
        // Check if the order items are empty
        $orderItems = Order_items::where('order_id', $order->id)->get();
        if ($orderItems->isEmpty()) {
            $order->delete();
            return response()->json([
                'status' => 0,
                'message' => 'The order is empty , please check your cart ,this product : ' . implode('- ', $unavailable_items) . ' not available in sufficient quantity, you can check another color or another size  ',
            ]);
        }
        //clear the Variant_cart
        Variant_cart::where('cart_id',  $user_cart->id)->forceDelete();
        // Return message indicating which items are not available
        if (!empty($unavailable_items)) {
            return response()->json([
                'status' => 0,
                'message' => 'your order is confirmed without this product : ' . implode($unavailable_items) . ' not available in sufficient quantity, you can check another color or another size ,Total price is :' . $order->Total_Price,
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'The order confirmed successfully ',
            'price' => $order->Total_Price,
        ]);
    }



    public function check_items()
    {
        $user = Auth::guard('user_api')->user();
        $user_cart = Cart::where('user_id', $user->id)->first();
        // Get the variant cart items
        $variantCarts = Variant_cart::where('cart_id', $user_cart->id)->get();
        $super_admin = SuperAdmin::first();

        $unavailable_items = [];
        $total_price = 0;
        foreach ($variantCarts as $variantCart) {
            // Get the variant
            $variant = Variant::find($variantCart->variant_id);
            $product = Product::find($variant->product_id);
            $color = Color::find($variant->color_id);
            $size = Size::find($variant->size_id);
            if ($variantCart->quantity > $variant->variant_quantity) {
                $unavailable_items[] = [
                    'product_name' => $product->name,
                    'color' => $color->color,
                    'size' => $size->size,
                    'quantity_in_cart' => $variantCart->quantity,
                    'quantity_in_stock' => $variant->variant_quantity,
                ];
            } else {
                if ($product->discount_percentage == 0) {
                    $total_price += ($product->price) * ($variantCart->quantity);
                } else {
                    $new_Price = ($product->price) - ($product->price) * ($product->discount_percentage) / 100;
                    $total_price += ($new_Price) * ($variantCart->quantity);
                }
            }
        }
        if (!empty($unavailable_items)) {
            $message = 'The following variants have insufficient quantity: ';
            foreach ($unavailable_items as $variant) {
                $message .= "*name: {$variant['product_name']} , ";
                $message .= "color: {$variant['color']} ,";
                $message .= "size: {$variant['size']} ,";
                $message .= "Quantity in cart: {$variant['quantity_in_cart']} ,";
                $message .= "Quantity in stock: {$variant['quantity_in_stock']},,,, ";
            }
            return response()->json([
                'status' => 0,
                'message' => $message,
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'All the item is available , you can confirmed your order',
            'price' => $total_price,
        ]);
    }

    public function get_user_orders()
    {
        $user = Auth::guard('user_api')->user();
        $user = User::find($user->id);
        $orders= $user->orders()
            ->with(['orderItems' => function ($query) {
                $query->with(['variant' => function ($query) {
                    $query->with(['product', 'color', 'size']);
                }]);
            }, 'orderItems.variant.product.productImages'])
            //,'product.productImages','product.productTags','product.productVariants'])
            ->get();
        return response()->json([
            'status' => 1,
            'data' => $orders,
        ]);

    }

    public function Orders_History_for_all_users()
    {
        $order = Order::with(['user', 'orderItems' => function ($query) {
            $query->with(['variant' => function ($query) {
                $query->with(['product', 'color', 'size']);
            }]);
        }, 'orderItems.variant.product.productImages'])
            ->whereYear('created_at', date('Y'))
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'status' => 1,
            'data' => $order
        ]);
    }


public function updateState(Request $request,$order_id){
$request->validate([
    'state'=>'required|boolean',
]);
$order=Order::where('id',$order_id);

if (!isset($order)) {
    return response()->json([
        'status' => 0,
        'message' => 'there is no order with this id '
    ]);
}
$order->update(['state' => $request->state]);


return response()->json([
    'status' => 1,
    'message' => 'order completed successfully'

]);
}


}
