<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function getUsers()
    {
        return User::all();
    }

    public function getUser_details($id)
    {
        $User = DB::table('users')->where('id', $id)->first();
        return $User;
    }

    public function getAdmins()
    {
        return Admin::where('state', false)->get();
    }

    public function getAdmin_details($id)
    {
        $admin = DB::table('admins')->where('state', false)->where('id', $id)->first();
        return $admin;
    }

    public function get_Total_Users()
    {
        $Total_Users= User::count();

        return[
            'Total of Users is: '=> $Total_Users,
        ];
    }

    public function get_Total_Admin()
    {
        $Total_Admins= Admin::count();

        return[
            'Total of Admins is: '=> $Total_Admins,
        ];
    }
    public function get_wallet_Admin()
    {
       return Admin::select('company_name', 'wallet')->get();

    }




}
