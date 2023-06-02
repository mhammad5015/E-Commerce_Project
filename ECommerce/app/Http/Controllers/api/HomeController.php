<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function getUsers()
    {
        return User::all();
    }

    public function userProfile($id)
    {
        $User = DB::table('users')->where('id', $id)->first();
        return $User;
    }

    public function getAllAdmins()
    {
        return Admin::where('state', false)->get();
    }

    public function adminProfile($id)
    {
        $admin = DB::table('admins')->where('state', false)->where('id', $id)->first();
        return $admin;
    }

    public function usersCount()
    {
        $Total_Users = User::count();
        return [
            'Total of Users is: ' => $Total_Users,
        ];
    }

    public function adminsCount()
    {
        $Total_Admins = Admin::count();
        return [
            'Total of Admins is: ' => $Total_Admins,
        ];
    }
    public function getAdminWallet()
    {
        return Admin::select('company_name', 'wallet')->get();
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
            return [
                'admin' => $admin,
                'add' => $ads,
            ];
        } else {
            return [
                'message' => 'admin is not found',
            ];
        }
    }

    public function delete_ads($id)
    {
        $ad = Ad::find($id);
        if ($ad) {
            $ad->delete();
        } else {
            return ['message' => 'ad is not found'];
        }
        return ['message' => 'ad deleted successfully'];
    }

    public function get_ads()
    {
        return Ad::with('admin')->get();
    }
}
