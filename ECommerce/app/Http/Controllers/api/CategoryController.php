<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){

        $category=Category::withDepth()
        ->get()
        ->toTree();

        return $category;
    }



    public function store(Request $request){
        $request->validate([
             'name'=>'required|unique:categories,name',
             'image'=>['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],
         ]);

         $input=$request->all();
         $input['image'] = 'storage/'. $request->file('image')->store('images', 'public');
         $category=Category::create($input);

         if($request->parent_id && $request->parent_id != null){

            $parent=Category::find($request->parent_id);

            if($parent ==null){
             return ['message' => 'parent is not found'];
            }
            //used to adda new node to tree structure
            $parent->appendNode($category);
         }

         return $category;
     }

     public function delete( $id){
        $node = Category::find($id);
        if ($node) {
            $node->delete();

        } else {
           return ['message' => 'category is not found'];
        }

         return ['message' => 'category deleted successfully'];

     }

     public function update(Request $request,$id)
     {

        $data = $request->validate([
            'name'=>'required',
            'image'=>['image', 'mimes:jpeg,png,gif,bmp,jpg,svg'],

        ]);



        $category = Category::query()->firstWhere('id',$id);

        if ($category) {
            $data['image'] = 'storage/'. $request->file('image')->store('images', 'public');
            $data['image'] = str_replace('public/', '', $data['image']);

            $category->update([
                'name'=>$data['name'],
                'image' => $data['image'],

            ]);
        } else {
           return ['message' => 'category is not found'];
        }

        if($request->parent_id && $request->parent_id != null){

            $parent=Category::find($request->parent_id);

            if($parent ==null){
             return ['message' => 'parent is not found'];
            }
            //used to add a new node to tree structure
            $parent->appendNode($category);
         }

         return [
            'message'=>'category updated successfully',
            'category'=> $category,
        ];
     }


     ///////////////////////////////////////////////////



    public function get_category_withProduct_Admin($admin_id){
        $admin=Admin::find($admin_id);
      //  $categoriesWithProducts =$admin->categoriesWithProducts->toTree()->first();

        $categories = Category::whereHas('products', function ($query) use ($admin_id ) {
           $query->where('admin_id', $admin_id);

        })
        ->with(['products' => function ($query) use ($admin_id) {
            $query->where('admin_id', $admin_id);
        }])
        ->with('ancestors')
        ->get()->toTree();

        return [
            'Company name is: '=> $admin->company_name,
            'The category With Products: '=>  $categories,

        ];

    }


    public function get_Categories_WithProductsForAdmin($admin_id)
    {
        $categoryIds= Product::where('admin_id', $admin_id)
                      ->pluck('category_id');

       $allCategoryIds= Category::whereIn('id', $categoryIds)
                      ->orWhere('_lft', '<', Category::whereIn('id', $categoryIds)->min('_lft'))
                      ->orWhere('_rgt', '>', Category::whereIn('id', $categoryIds)->max('_rgt'))
                      ->pluck('id');

       $categories = Category::with(['products' => function($query) use ($admin_id) {
                        $query->where('admin_id', $admin_id);
                    }])
                    ->whereIn('id', $allCategoryIds)
                    ->get()->toTree();

      return $categories;

    }


    public function get_Parent_Category($category_id){
        // $category_id = [3,6];
        // return Category::whereIn('id', $category_id)
        // ->with('ancestors')
        // ->get();

        $category = Category::find($category_id);
        return $category ->get()->toTree();

    }


}
