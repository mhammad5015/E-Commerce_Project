<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Category_image;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // public function create_category(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|unique',
    //         'image' => 'required|image|mimes:jpeg,png,gif,bmp,jpg,svg',
    //     ]);
    //     $category = new Category();
    //     $category->name = $request->name;
    //     $category->save();
    //     $cat_img = new Category_image();
    //     $cat_img->image = 'storage/' . $request->file('image')->store('images', 'public');
    //     $cat_img->save();
    //     return response()->json([
    //         'message' => 'Success'
    //     ]);
    // }
}
