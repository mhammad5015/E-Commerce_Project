<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::withDepth()
            ->get()
            ->toTree();
        return $category;
    }

    public function get_all_categories_with_produts()
    {
        $category = Category::with(['products' => function ($query) {
            $query->where('approved', 1);
        }, 'products.admin', 'products.productImages', 'products.productTags', 'products.productVariants'])
            ->get()
            ->toTree();

        if (!isset($category)) {
            return response()->json([
                'status' => 0,
                'data' => $category
            ]);
        }
        return response()->json([
            'status' => 1,
            'data' => $category
        ]);
    }


    public function create_category(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name',
            'image' => ['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
        ]);
        $input = $request->all();
        if (isset($input['image'])) {
            $input['image'] = 'storage/' . $request->file('image')->store('images', 'public');
        }
        $category = Category::create($input);
        if ($request->parent_id != null) {
            $parent = Category::find($request->parent_id);
            if ($parent == null) {
                return ['message' => 'parent is not found'];
            }
            //used to adda new node to tree structure
            $parent->appendNode($category);
        }
        return $category;
    }

    public function delete($id)
    {
        $node = Category::find($id);
        if ($node) {
            $node->delete();
        } else {
            return ['message' => 'category is not found'];
        }
        return ['message' => 'category deleted successfully'];
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'image' => ['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
        ]);
        $category = Category::query()->firstWhere('id', $id);
        if ($category) {
            $data['image'] = 'storage/' . $request->file('image')->store('images', 'public');
            $data['image'] = str_replace('public/', '', $data['image']);
            $category->update([
                'name' => $data['name'],
                'image' => $data['image'],
            ]);
        } else {
            return ['message' => 'category is not found'];
        }
        if ($request->parent_id && $request->parent_id != null) {
            $parent = Category::find($request->parent_id);
            if ($parent == null) {
                return ['message' => 'parent is not found'];
            }
            //used to add a new node to tree structure
            $parent->appendNode($category);
        }
        return [
            'message' => 'category updated successfully',
            'category' => $category,
        ];
    }


    ///////////////////////////////////////////////////


    public function get_Categories_WithProductsForAdmin($admin_id)
    {
        $exists = Admin::where('id', $admin_id)->exists();
        if(!$exists){
            return response()->json([
                'status' => 0,
                'message' => 'there is no vendor with this id',
            ]);
        }
        $categoryIds = Product::where('admin_id', $admin_id)
            ->pluck('category_id');
        $s = Product::where('admin_id', $admin_id)->first();
        if (!isset($s)) {
            return response()->json([
                'status' => 0,
                'message' => $s,
            ]);
        }
        $allCategoryIds = Category::whereIn('id', $categoryIds)
            ->orWhere('_lft', '<', Category::whereIn('id', $categoryIds)->min('_lft'))
            ->orWhere('_rgt', '>', Category::whereIn('id', $categoryIds)->max('_rgt'))
            ->pluck('id');

        $categories = Category::with(['products' => function ($query) use ($admin_id) {
            $query->where('admin_id', $admin_id)->where('approved', 1);
        }, 'products.productImages'])
            ->whereIn('id', $allCategoryIds)
            ->get()->toTree();
        return response()->json([
            'status' => 1,
            'data' => $categories
        ]);
    }


    public function get_Parent_Category($category_id)
    {
        $category = Category::find($category_id);
        return $category->get()->toTree();
    }


    public function getAllChildren()
    {
        // $children = DB::table('categories')
        //     ->whereNotNull('parent_id')
        //     ->get();
        $children = DB::table('categories as c')
            ->Join('categories as p', 'c.parent_id', '=', 'p.id')
            ->select('c.*', 'p.name as parent_name')
            ->whereNotNull('c.parent_id')
            ->get();

        $null = Category::where("parent_id", NULL)->first();

        if (!isset($null)) {
            return response()->json([
                'status' => 0,
                'message' => 'there is no category to show'
            ]);
        }
        return response()->json([
            'status' => 1,
            'data' => $children
        ]);
    }
}
