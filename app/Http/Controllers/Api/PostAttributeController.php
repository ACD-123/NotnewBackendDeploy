<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\PostAttribute;
use Illuminate\Http\Request;
use DB; 
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\File;

class PostAttributeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories=PostAttribute::get();
            $categories->each(function ($category){
                if($category->type=="select")
                {
                    $category->options=explode(",",$category->options);
                }
            });
            return customApiResponse(true,["attribute"=>$categories],"Attributes");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function getByGuid(Request $request,$guid)
    {
        try {
            $category=PostAttribute::where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Attribute Not Found!', 404);
            }
            if($category->type=="select")
            {
                $category->options=explode(",",$category->options);
            }
            return customApiResponse(true,["attribute"=>$category],"Attribute");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function create(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'name' => 'required',
                'type'=>'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $options=implode(",",json_decode($input['options']??"[]"));
            $category=PostAttribute::create([
                'name'=>$input['name'],
                'type'=>$input['type'],
                'options'=>$options,
                'guid'=>Str::uuid()
            ]);
            return customApiResponse(true,[],"Attribute Created Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function update($guid,Request $request)
    {
        try {
            $category=PostAttribute::where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            $input = $request->all();
            $rules = [
                'name' => 'required',
                'type'=>'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $options=implode(",",json_decode($input['options']??"[]"));
            $category->update([
               'name'=>$input['name'],
                'type'=>$input['type'],
                'options'=>$options,
            ]);
            return customApiResponse(true,[],"Attribute Updated Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    
}