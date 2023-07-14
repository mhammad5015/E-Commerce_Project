<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Admin;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function getUsers()
    {
        return response()->json([
            'data' => User::all()
        ]);
    }

    public function userProfile($id)
    {
        $User = DB::table('users')->where('id', $id)->first();
        return response()->json([
            'data' => $User
        ]);
    }

    public function getAllAdmins()
    {
        return response()->json([
            'data' => Admin::where('state', false)->get()
        ]);
    }

    public function adminProfile($id)
    {
        $admin = DB::table('admins')->where('state', false)->where('id', $id)->first();
        return response()->json([
            'data' => $admin
        ]);
    }

    public function usersCount()
    {
        $Total_Users = User::count();
        return response()->json([
            'Total of Users is: ' => $Total_Users
        ]);
    }

    public function adminsCount()
    {
        $Total_Admins = Admin::count();
        return response()->json([
            'Total of Admins is: ' => $Total_Admins,
        ]);
    }
    public function getAdminWallet()
    {
        return response()->json([
            'data' => Admin::select('company_name', 'wallet')->get()
        ]);
    }

    // ads
    public function store_ad(Request $request)
    {
        $input = $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'image' => ['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
        ]);
        $admin = Admin::find($input['admin_id']);
        if ($admin) {
            $ads = new Ad();
            $imagePath = $request->file('image')->store('images', 'public');
            $ads->image = 'storage/' . $imagePath;
            $admin->adds()->save($ads);
            return response()->json([
                'admin' => $admin,
                'add' => $ads,
            ]);
        } else {
            return response()->json([
                'message' => 'admin is not found',
            ]);
        }
    }

    public function delete_ads($id)
    {
        $ad = Ad::find($id);
        if ($ad) {
            $ad->delete();
        } else {
            return response()->json([
                'message' => 'ad is not found'
            ]);
        }
        return response()->json([
            'message' => 'ad deleted successfully'
        ]);
    }

    public function get_ads()
    {
        return response()->json([
            'data' => Ad::with('admin')->get()
        ]);
    }

    // Favorites
    public function add_to_favorites($product_id)
    {
        $user = Auth::guard('user_api')->user();
        $favorites = Favorite::get();
        foreach ($favorites as $item) {
            if ($item->user_id == $user->id && $item->product_id == $product_id) {
                return response()->json([
                    "status" => 0,
                    "message" => 'The Product already in your favorites list',
                ]);
            }
        }
        $favorite = Favorite::create([
            "product_id" => $product_id,
            "user_id" => $user->id
        ]);
        return response()->json([
            "status" => 1,
            "message" => "Product Added Successfully To Your Favorites List",
            "data" => $favorite
        ], 200);
    }

    public function remove_from_favorites($product_id)
    {
        $favorite = Favorite::where('product_id', $product_id)->first();
        if (!isset($favorite)) {
            return response()->json([
                'status' => 0,
                'message' => 'there is no product with this id to remove'
            ]);
        }
        Favorite::where('product_id', $product_id)->forceDelete();
        return response()->json([
            'status' => 1,
            'message' => 'Product Removed from The Favorites List'
        ]);
    }

    public function get_all_favorites()
    {
        $favorites = Favorite::all();
        return response()->json([
            'status' => 1,
            'data' => $favorites
        ]);
    }

    // Rates
    public function add_rate(Request $request, $product_id)
    {
        $request->validate([
            'rate' => 'required|min:1|max:5'
        ]);
        $user = Auth::guard('user_api')->user();
        $rates = Rate::all();
        foreach ($rates as $item) {
            if ($item->product_id == $product_id && $item->user_id == $user->id) {
                $item->update(['rate' => $request->rate]);
                return response()->json([
                    'status' => 1,
                    'message' => 'Rate Updated successfully'
                ]);
            }
        }
        $rate = Rate::create([
            "product_id" => $product_id,
            "user_id" => $user->id,
            "rate" => $request->rate,
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'Rate Added successfully'
        ]);
    }

    public function show_rate($product_id)
    {
        $sum = Rate::where('product_id', $product_id)->sum('rate');
        $count = Rate::where('product_id', $product_id)->count();
        if ($count != 0) {
            return response()->json([
                'status' => 1,
                'rate' => $sum / $count
            ], 200);
        } else {
            return response()->json([
                'status' => 1,
                'rate' => 0
            ], 200);
        }
    }

    // Comments
    public function add_comment(Request $request, $product_id)
    {
        $request->validate([
            'comment' => 'required'
        ]);
        $user = Auth::guard('user_api')->user();
        $comment = Comment::create([
            'product_id' => $product_id,
            'user_id' => $user->id,
            'comment' => $request->comment
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'comment added successfully'
        ]);
    }

    public function delete_comment($comment_id)
    {
        $comment = Comment::where('id', $comment_id)->first()->forceDelete();
        return response()->json([
            'status' => 1,
            'message' => 'Comment Deleted Successfully'
        ]);
    }
    public function get_product_comments(Request $request, $product_id)
    {
        $comments = Comment::where('product_id', $product_id)->get();
        return response()->json([
            'status' => 1,
            'data' => $comments
        ]);
    }
}
