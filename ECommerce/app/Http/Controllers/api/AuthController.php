<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\SendCodeResetPassword;
use App\Models\Address;
use App\Models\Admin;
use App\Models\ResetCodePassword;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //== USER ==//
    // USER REGISTER
    public function userRegister(Request $request)
    {
        // validation
        $request->validate([
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone_number' => 'required|min:9|max:10|unique:users',
            'profile_img_url' => ['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
            'address' => 'required',
        ]);
        // inserting in database
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['token'] = str::random(60);
        $input['profile_img_url'] = 'storage/' . $request->file('profile_img_url')->store('images', 'public');
        $user = User::create($input);
        $accessToken = $user->createToken('MyApp', ['user'])->accessToken;
        // sending response
        return response()->json([
            'data' => $user,
            'accessToken' => $accessToken,
        ]);
    }


    // USER LOGIN
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where("email", $request->email)->first();
        if (isset($user)) {
            if (Hash::check($request->password, $user->password)) {
                // create token
                $token = $user->createToken('MyApp', ['user'])->accessToken;
                // send response
                return response()->json([
                    "status" => 1,
                    "message" => "User Logged In Succesfully ",
                    "data" => $user,
                    "token" => $token
                ]);
            } else {
                return response()->json([
                    "status" => 0,
                    "messege" => "Password didn't match"
                ]);
            }
        }
        return response()->json([
            "status" => 0,
            "message" => "Email Not Found"
        ]);
    }


    //USER LOGOUT
    public function userLogout(Request $request)
    {
        Auth::guard('user_api')->user()->token()->revoke();
        return response()->json([
            'status' => 1,
            'message' => 'User logged out successfuly'
        ]);
    }


    // USER FORGET PASSWORD
    public function user_forgetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        //Delete all old code that user send before
        ResetCodePassword::query()->where('email', $data['email'])->delete();
        //generate randome code
        $data['code'] = mt_rand(100000, 999999);
        //create new code
        $codeData = ResetCodePassword::query()->create($data);
        //send email to user
        Mail::to($data['email'])->send(new SendCodeResetPassword($data));
        return response()->json([
            'message' => 'code sent',
            'code' => $data['code'],
        ], 401);
    }


    // CHECK CODE
    public function checkCode(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
        ]);
        //find the code
        $passwordReset = ResetCodePassword::query()->firstWhere('code', $data['code']);
        //check if it is not expire
        if ($passwordReset['created_at'] > now()->addHour()) {
            $passwordReset->delete();
            return response()->json(['message' => 'code is expire,please return again'], 422);
        }
        return response()->json([
            'code' => $passwordReset['code'],
            'message' => 'password code is valid',
        ]);
    }


    // USER RESET PASSWORD
    public function userResetPassword(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => ['required', 'confirmed'],
        ]);
        //find the code
        $passwordReset = ResetCodePassword::query()->firstWhere('code', $data['code']);
        //check if it is not expire
        if ($passwordReset['created_at'] > now()->addHour()) {
            $passwordReset->delete();
            return response()->json(['message' => 'code is expire,please return again'], 422);
        }
        $user = User::query()->firstWhere('email', $passwordReset['email']);
        //update password
        $data['password'] = bcrypt($data['password']);
        $user->update([
            'password' => $data['password'],
        ]);
        $passwordReset->delete();
        return response()->json(['message' => 'password has been reset successfully']);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////


    //== ADMIN ==//
    // ADMIN LOGOUT
    public function adminLogout(Request $request)
    {
        Auth::guard('admin_api')->user()->token()->revoke();
        return response()->json([
            'status' => true,
            'message' => 'Admin Logged Out Successfuly'
        ]);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////


    //== SUPER ADMIN ==//
    // SUPER_ADMIN LOGOUT
    public function super_adminLogout(Request $request)
    {
        Auth::guard('super_admin_api')->user()->token()->revoke();
        return response()->json([
            'status' => true,
            'message' => 'super admin logged out successfuly'
        ]);
    }

    public function add_admin(Request $request)
    {
        // validation
        $request->validate([
            'company_name' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required|confirmed',
            'phone_number' => 'required|min:9|max:10',
            'address' => 'required',
            'logo' => ['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
            'Commercial_Record' => 'required|mimes:doc,pdf,docx,jpg,jpeg,png,gif|max:10000',
        ]);
        // inserting in admins table
        $input = $request->except('address');
        $input['password'] = Hash::make($input['password']);
        $input['token'] = Str::random(60);
        $input['logo'] = 'storage/' . $request->file('logo')->store('images', 'public');
        $input['Commercial_Record'] = 'storage/' . $request->file('Commercial_Record')->store('files', 'public');
        $admin = Admin::create($input);
        $addresses = $request->only('address');
        // inserting in addresses table
        foreach ($addresses as $addr) {
            foreach ($addr as $a) {
                $address[] = DB::table('addresses')->insert([
                    'admin_id' => $admin->id,
                    'address' => $a
                ]);
            }
        }
        // get admin profile
        $admin_data = $admin->load('addresses');
        // sending response
        return response()->json([
            'admin_data' => $admin_data,
        ]);
    }


    ////////////////////////////////////////////////////////////////////////////////////


    // DASHBOARD LOGIN
    public function dashboard_login(Request $request)
    {
        // validation
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);
        // check admin
        $admin = Admin::where("email", $request->email)->first();
        if (isset($admin)) {
            if (Hash::check($request->password, $admin->password)) {
                // create token
                $token = $admin->createToken('MyApp', ['admin'])->accessToken;
                // send response
                return response()->json([
                    "status" => 1,
                    "is_super_admin" => 0,
                    "message" => "Admin Loged In Succesfully ",
                    "data" => $admin,
                    "token" => $token,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
                    "messege" => "Password didn't match"
                ]);
            }
        } else {
            // check super_admin
            $super_admin = SuperAdmin::where("email", $request->email)->first();
            if (isset($super_admin)) {
                if (Hash::check($request->password, $super_admin->password)) {
                    // create token
                    $token = $super_admin->createToken('MyApp', ['super_admin'])->accessToken;
                    // send response
                    return response()->json([
                        "status" => 1,
                        "is_super_admin" => 1,
                        "message" => "Super_admin Loged In Succesfully",
                        "data" => $super_admin,
                        "token" => $token,
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
                        "message" => "Password Didn't Match"
                    ]);
                }
            }
        }
        return response()->json([
            "status" => 0,
            "message" => "Email Not Found"
        ]);
    }
}
