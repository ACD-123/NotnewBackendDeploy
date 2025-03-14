<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\CategoryAttributes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function index(Request $request)
    {
         return Category::with('media')->whereNull('parent_id')->where('active',1)->get();
    }
    
    public function indexUnderage(Request $request)
    {
         return Category::with('media')->whereNull('parent_id')->where('active',1)->where('underage',1)->get();
    }
    public function indexHome(Request $request)
    {
         return Category::with('media')->whereNull('parent_id')->where('active',1)->where('underage',0)->get();
    }
    
    public function recursive(Request $request)
    {
        return Category::select(['id', 'name', 'guid', 'description'])
            ->with(['media'])
            ->with(['attributes'])
            ->where('parent_id', '=', null)
            ->where('active','=',true)
            // ->where('type', $request->get('type') == 1 ? Category::PRODUCT : Category::SERVICE)
            ->with(['childrenRecursive' => function (HasMany $hasMany) {
                $hasMany->select(['id', 'name', 'parent_id'])->with(['attributes']);
            }])
            ->get();
    }

    public function all(Request $request)
    {
        return Category::select(['id', 'name', 'guid', 'description'])
            ->with(['media'])
            ->where('parent_id', '=', null)
            ->where('active','=',true)
            // ->where('type', $request->get('type') == 1 ? Category::PRODUCT : Category::SERVICE)
            ->with(['childrenRecursive' => function (HasMany $hasMany) {
//                $hasMany->select(['id', 'name', 'parent_id']);
            }])
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $category = new Category();
        $category->fill($request->all())->save();
        return response()->json([
            'message' => 'Category added'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @return Category
     */
    public function show(Category $category)
    {
        return $category->loadMissing('attributes');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function productAttributes(Category $category)
    {
        // $category = Category::where('id', $category->id)->first();
        //     // ->join('category_attributes','category_attributes.category_id','=','categories.id')
        // $categoryattributes = CategoryAttributes::where('category_id', $category->id)->get();
        // $attributesids = [];
        // $unitids = [];
        // foreach($categoryattributes as $categoryattribute){
        //     array_push($attributesids, $categoryattribute->attribute_id);    
        // }
        // $attributes = Attribute::whereIn('id',$attributesids)->get();
       
        // if($attributes){
        //     return response()->json(['status'=> true,'data' =>$attributes], 200);       
        // }else{
        //     return response()->json(['status'=> false,'data' =>"Unable To Get Attriubtes"], 400);       
        // }
        
        //     // ->with('category')
        //     // ->with('attribute')
        //     // ->with('unitType')
        //     // ->first();
        //     // return [
        //     //     'category' => $category,
        //     //     'attributes'=> $attrbutes
        //     //     ];
        // // return $category->categoryAttributes()
        // //     ->with(['attribute', 'unitType'])
        // //     ->get();
        // return $category->categoryAttributes()
        //     ->with(['attribute', 'unitType'])
        //     // ->with(['attribute'])
        //     ->get();
        $category = Category::where('id', $category->id)->first();
            // ->join('category_attributes','category_attributes.category_id','=','categories.id')
        $categoryattributes = CategoryAttributes::where('category_id', $category->id)->get();
        if($categoryattributes){
            $attributesids = [];
            $unitids = [];
            foreach($categoryattributes as $categoryattribute){
                array_push($attributesids, $categoryattribute->attribute_id);    
            }
            $attributes = Attribute::whereIn('id',$attributesids)->get();
            if($attributes){
                return response()->json(['status'=> true,'data' =>$attributes], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"Unable To Get Attriubtes"], 400);       
            }
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Attriubtes"], 400);       
        }
    }

    // public function categoryAttributes(Request $request,)
    // {
    //     return $category->categoryAttributes()
    //         ->with(['attribute', 'unitType'])
    //         ->get();
    // }


    public function tabs(Request $request)
    {
        return Category::select(['id', 'name', 'description'])
            ->where('parent_id', '=', null)
            ->where($this->applyFilters($request))
            ->get()
            ->map(function ($category) {
                return [
                    'key' => "$category->id",
                    'tab' => $category->name
                ];
            });
    }
    
     public function getSubCategories($categoryId)
    {
        try {
            $parentCategoryName="";
           $category = Category::with('media')->where('id', $categoryId)->first();
            $subCategories = Category::with('media')->where('parent_id', $categoryId)->where('active',1)->get();
            if($category->parent_id != NULL && $category->parent_id != ""){
               $parent=Category::where('id', $category->parent_id)->first();
               $parentCategoryName=$parent->name;
            }
            
            return response()->json(['status' => true, 'message' =>"Sub Categories!","category_name"=>$category->name, 'data' =>["category"=>$category,"sub_categories"=>$subCategories,"parent_name"=>$parentCategoryName]], 200);
        }
        catch(\Exception $e)
        {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []], 400);
        }
    }
}
