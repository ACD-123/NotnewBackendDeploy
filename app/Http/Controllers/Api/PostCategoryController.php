<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\PostAttribute;
use App\Models\PostCategoryAttribute;
use Illuminate\Http\Request;
use DB; 
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\File;

class PostCategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories=PostCategory::where('status',1)->get();
            return customApiResponse(true,["categories"=>$categories],"Categories");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function getByGuid(Request $request,$guid)
    {
        try {
            $category=PostCategory::where('status',1)->where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            return customApiResponse(true,["category"=>$category],"Category");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function create(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'title' => 'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $category=PostCategory::create([
                'title'=>$input['title'],
                'guid'=>Str::uuid()
            ]);
            $destinationPath = public_path('postCategories');
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imageName);
                $category->image = 'public/postCategories/' . $imageName;
                $category->save();
            }
            return customApiResponse(true,[],"Category Created Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function update($guid,Request $request)
    {
        try {
            $category=PostCategory::where('status',1)->where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            $input = $request->all();
            $rules = [
                'title' => 'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $category->update([
                'title'=>$input['title']
            ]);
            $destinationPath = public_path('postCategories');
            if ($request->hasFile('image')) {
                if ($category->image && file_exists(public_path($category->image))) {
                    unlink($category->image);
                }
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imageName);
                $category->image = 'public/postCategories/' . $imageName;
                $category->save();
            }
            return customApiResponse(true,[],"Category Updated Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function updateStatus($guid,Request $request)
    {
        try {
            $category=PostCategory::where('status',1)->where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            $category->update([
                'status'=> $category->status==1 ? 0 : 1
            ]);
            return customApiResponse(true,[],"Category Status Updated Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function assignAttributes(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'category_id' => 'required',
                'attribute_ids' => 'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $category=PostCategory::where('id',$request->category_id)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            PostCategoryAttribute::where('category_id',$category->id)->delete();
            $attributes=json_decode($request->attribute_ids);
            foreach ($attributes as $value) {
                $attribute=PostAttribute::where('id',$value)->first();
                if($attribute){
                    PostCategoryAttribute::create([
                        'category_id'=>$category->id,
                        'attribute_id'=>$value
                    ]);
                }
            }
            return customApiResponse(true,[],"Category Attribute Updated Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function getCategoryAttributes(Request $request,$guid)
    {
        try {
            $category=PostCategory::where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            $categoryAttributes = PostCategoryAttribute::where('category_id', $category->id)->get();
            $attributes = [];
            if ($categoryAttributes) {
                foreach ($categoryAttributes as $value) {
                    $attribute = PostAttribute::find($value->attribute_id);
                    $data = [
                        "id"=>$attribute->id,
                        "key"=>$attribute->name,
                        "type"=>$attribute->type,
                       "options" => isset($attribute->options)?explode(",",$attribute->options):[]
                    ];
                    $attributes[] = $data;
                }
            }
            return customApiResponse(true,["attributes"=>$attributes],"Category Wise Attributes!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
}