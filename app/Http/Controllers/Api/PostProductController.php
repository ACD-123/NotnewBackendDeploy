<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\PostProduct;
use App\Models\PostProductFeature;
use App\Models\PostProductAttribute;
use App\Models\PostProductImage;
use App\Models\AdminSetting;
use App\Models\PostTransaction;
use Illuminate\Http\Request;
use DB; 
use Carbon\Carbon; 
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Favourite;

class PostProductController extends Controller
{
    public function home(Request $request)
    {
        try {
            $bearerToken = $request->bearerToken();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $delivery=$request->delivery;
            $latitude=$request->latitude;
            $longitude=$request->longitude;
            $distance=$request->distance;
            $categoriesQuery = PostCategory::whereHas('products', function ($query) use ($bearerToken,$delivery,$latitude,$longitude,$distance) {
                $query->where('status', 1);
                if (!$bearerToken) {
                    $query->where('underage', 0);
                }
                if(isset($delivery) && !empty($delivery) && $delivery!=null && $delivery!="undefined" && $delivery!="null" && $delivery>0) {
                    $query->where('delivery_method',$delivery);
                }
                if(isset($latitude) && !empty($latitude) && $latitude!=null && $latitude!="undefined" && $latitude!="null" && isset($longitude) && !empty($longitude) && $longitude!=null && $longitude!="undefined" && $longitude!="null" && $distance > 0) {
                    $query->whereRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                        [$latitude, $longitude, $latitude, $distance]
                    );
                }
            });
            $total = $categoriesQuery->count();
            $categories = $categoriesQuery->with(['products' => function ($query) use ($bearerToken,$delivery,$latitude,$longitude,$distance) {
                $query->where('status', 1);
                if (!$bearerToken) {
                    $query->where('underage', 0);
                }
                if(isset($delivery) && !empty($delivery) && $delivery!=null && $delivery!="undefined" && $delivery!="null" && $delivery>0) {
                    $query->where('delivery_method',$delivery);
                }
                if(isset($latitude) && !empty($latitude) && $latitude!=null && $latitude!="undefined" && $latitude!="null" && isset($longitude) && !empty($longitude) && $longitude!=null && $longitude!="undefined" && $longitude!="null" && $distance > 0) {
                    $query->whereRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                        [$latitude, $longitude, $latitude, $distance]
                    );
                }
                $query->orderBy('promoted', 'desc')->orderBy('created_at', 'desc');
                $query->take(10);
            },'products.extra'])->skip($skip)->take($page_size)->get();
        $total_pages = ceil($total / $page_size);
        $pagination = [
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'total_pages' => $total_pages,
            'remaining' => $total_pages - $page,
            'next_page' => $total_pages > $page ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ];
        return customApiResponse(true,["categories"=>$categories,"pagination"=>$pagination],"Products");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function filter(Request $request)
    {
        try {
            $bearerToken = $request->bearerToken();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $categories=$request->categories;
            $promoted=$request->promoted;
            $latest=$request->latest;
            $lowest=$request->lowest;
            $highest=$request->highest;
            $fromPrice=$request->from_price;
            $toPrice=$request->to_price;
            $fromPriceGiven=false;
            $toPriceGiven=false;
            $search_key=$request->search_key;
            $sortBy=$request->sort_by;
            $nearBy=$request->near_by;
            $latitude=$request->latitude;
            $longitude=$request->longitude;
            $distance=$request->distance;
            $productsQuery=PostProduct::with('category','extra','images','user','user.media')->where('status',1);
            if(isset($sortBy) && !empty($sortBy) && $sortBy!=null && $sortBy!="undefined" && $sortBy!="null") {
                if($sortBy=="best"){
                    $promoted=1;
                }
                if($sortBy=="latest"){
                    $latest=1;
                }
                if($sortBy=="highest"){
                    $highest=1;
                }
                if($sortBy=="lowest"){
                    $lowest=1;
                }
                if($sortBy=="nearBy"){
                    $nearBy=1;
                }
            }
            if(isset($categories) && !empty($categories) && $categories!=null && $categories!="undefined" && $categories!="null" && $categories!="all") {
                $categoriesArray=explode(",",$categories);
                $productsQuery->whereIn('post_category_id',$categoriesArray);
            }
            if (!$bearerToken) {
                $productsQuery->where('underage', 0);
            }
            if(isset($promoted) && !empty($promoted) && $promoted!=null && $promoted!="undefined" && $promoted!="null") {
                $productsQuery->where('promoted',$promoted);
            }
            if(isset($latest) && !empty($latest) && $latest!=null && $latest!="undefined" && $latest!="null" && $latest>0) {
                $productsQuery->orderBy('created_at','desc');
            }
            if(isset($lowest) && !empty($lowest) && $lowest!=null && $lowest!="undefined" && $lowest!="null" && $lowest>0) {
                $productsQuery->orderBy('price','asc');
            }
            if(isset($highest) && !empty($highest) && $highest!=null && $highest!="undefined" && $highest!="null" && $highest>0) {
                $productsQuery->orderBy('price','desc');
            }
            if(isset($search_key) && !empty($search_key) && $search_key!=null && $search_key!="undefined" && $search_key!="null") {
                $productsQuery->where('title','LIKE','%'.$search_key.'%');
            }
            if(isset($fromPrice) && !empty($fromPrice) && $fromPrice!=null && $fromPrice!="undefined" && $fromPrice!="null") {
                $fromPriceGiven=true;
            }
            if(isset($toPrice) && !empty($toPrice) && $toPrice!=null && $toPrice!="undefined" && $toPrice!="null") {
                $toPriceGiven=true;
            }
            if($toPriceGiven==true){
                $productsQuery->where('price','<=',$toPrice);
            }
            if($fromPriceGiven==true){
                $productsQuery->where('price','>=',$fromPrice);
            }
            if(isset($nearBy) && !empty($nearBy) && $nearBy !=null && $nearBy!="undefined" && $nearBy!="null" && $nearBy>0) {
                if(isset($latitude) && !empty($latitude) && $latitude !=null && $latitude!="undefined" && $latitude!="null") {
                    if(isset($longitude) && !empty($longitude) && $longitude !=null && $longitude!="undefined" && $longitude!="null") {
                        if(isset($distance) && !empty($distance) && $distance !=null && $distance!="undefined" && $distance!="null" && $distance>0) {
                            $productsQuery->whereRaw(
                                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                                [$latitude, $longitude, $latitude, $distance]
                            );
                        }
                    }
                }
            }
            $total = $productsQuery->count();
            $products=$productsQuery->orderBy('promoted','desc')->skip($skip)->take($page_size)->get();
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : null,
                'prev_page' => $page > 1 ? $page - 1 : null,
            ];
            return customApiResponse(true,["products"=>$products,"pagination"=>$pagination],"Filter Products Fetched Successfully!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        } 
    }
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $total = PostProduct::where('user_id', $user->id)->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $products=PostProduct::with('category','extra','images','user','user.media')->where('user_id', $user->id)->skip($skip)->take($page_size)->get();
            return customApiResponse(true,["products"=>$products,"pagination"=>$pagination],"Products");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function create(Request $request)
    {
        try {
            $user = $request->user();
            $input = $request->all();
            $rules = [
                'title' => 'required',
                'category_id' => 'required',
                'type' => 'required',
                'main_image'=>'required',
                'address'=>'required',
                'condition'=>'required',
                'price'=>'required',
                'delivery_method'=>'required',
                'underage'=>'required'  
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            DB::beginTransaction();
            $category=PostProduct::create([
                'title'=>$input['title'],
                'description'=>$input['description']??"",
                "post_category_id"=>$input['category_id'],
                "post_type"=>$input['type'],
                "address"=>$input['address'],
                "city"=>$input['city']??"",
                "country"=>$input['country']??"",
                "zip"=>$input['zip']??"",
                "latitude"=>$input['latitude']??"",
                "longitude"=>$input['longitude']??"",
                "condition"=>$input['condition'],
                "firm_price"=>$input['firm_price']??0,
                "virtual_tour"=>$input['virtual_tour']??0,
                "price"=>$input['price'],
                "delivery_method"=>$input['delivery_method'],
                "underage"=>$input['underage']??0,
                "user_id"=>$user->id,
                'guid'=>Str::uuid()
            ]);
            $destinationPath = public_path('postCategories');
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imageName);
                $category->main_image = 'public/postCategories/' . $imageName;
                $category->save();
            }
            if ($request->file('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('postCategories');
                    $image->move($destinationPath, $imageName);
                    PostProductImage::create([
                        "image" => 'public/postCategories/' . $imageName,
                        "post_product_id"=>$category->id
                    ]);
                }
            }
            $extraFeatures = json_decode($input['features'] ?? "[]");
            foreach ($extraFeatures as $feature) {
                PostProductFeature::create([
                    "title" => $feature,
                    "post_product_id"=>$category->id
                ]);
            }
            $postAttributes = json_decode($input['post_attributes'] ?? "[]");
            foreach ($postAttributes as $feature) {
                PostProductAttribute::create([
                    "product_id"=>$category->id,
                    "attribute_id"=>$feature->attribute_id,
                    "value"=>$feature->value
                ]);
            }
            DB::commit();
            return customApiResponse(true,["product"=>$category],"Product Created Successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function getByGuid(Request $request,$guid)
    {
        try {
            $category=PostProduct::with('category','extra','images','user','user.media')->where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            $productAttributes = PostProductAttribute::with('attribute')->where('product_id', $category->id)->get();
            $attributes = [];
            if ($productAttributes) {
                foreach ($productAttributes as $value) {
                    $data = [
                        "key"=>$value->attribute->name,
                        "type"=>$value->attribute->type,
                        "value"=>$value->value,
                       "options" => isset($value->attribute->options)?explode(",",$value->attribute->options):[]
                    ];
                    $attributes[] = $data;
                }
            }
            $category->product_attributes=$attributes;
            return customApiResponse(true,["product"=>$category],"Product!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function update(Request $request,$guid)
    {
        try {
            $user = $request->user();
            $input = $request->all();
            $rules = [
                'title' => 'required',
                'category_id' => 'required',
                'type' => 'required',
                'address'=>'required',
                'condition'=>'required',
                'price'=>'required',
                'delivery_method'=>'required',
                'underage'=>'required'  
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $product=PostProduct::where('guid',$guid)->where('user_id',$user->id)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            DB::beginTransaction();
            PostProductFeature::where('post_product_id',$product->id)->delete();
            $extraFeatures = json_decode($input['features'] ?? "[]");
            foreach ($extraFeatures as $feature) {
                PostProductFeature::create([
                    "title" => $feature,
                    "post_product_id"=>$product->id
                ]);
            }
            PostProductAttribute::where('product_id',$product->id)->delete();
            $postAttributes = json_decode($input['post_attributes'] ?? "[]");
            foreach ($postAttributes as $feature) {
                PostProductAttribute::create([
                    "product_id"=>$product->id,
                    "attribute_id"=>$feature->attribute_id,
                    "value"=>$feature->value
                ]);
            }
            $product->update([
                'title'=>$input['title']??$product->title,
                'description'=>$input['description']??$product->description,
                "post_category_id"=>$input['category_id']??$product->post_category_id,
                "post_type"=>$input['type']??$product->post_type,
                "address"=>$input['address']??$product->address,
                "city"=>$input['city']??$product->city,
                "country"=>$input['country']??$product->country,
                "zip"=>$input['zip']??$product->zip,
                "latitude"=>$input['latitude']??$product->latitude,
                "longitude"=>$input['longitude']??$product->longitude,
                "condition"=>$input['condition']??$product->condition,
                "firm_price"=>$input['firm_price']??$product->firm_price,
                "virtual_tour"=>$input['virtual_tour']??$product->virtual_tour,
                "price"=>$input['price']??$product->price,
                "underage"=>$input['underage']??$product->underage,
                "delivery_method"=>$input['delivery_method']??$product->delivery_method
            ]);
            $destinationPath = public_path('postCategories');
            if ($request->hasFile('main_image')) {
                if ($product->main_image && file_exists(public_path($product->main_image))) {
                    unlink($product->main_image);
                }
                $image = $request->file('main_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imageName);
                $product->main_image = 'public/postCategories/' . $imageName;
                $product->save();
            }
            $deleted_files = json_decode($input['deleted_files'] ?? "[]");
            foreach ($deleted_files as $file) {
                $productImage=PostProductImage::find($file);
                unlink($productImage->image);
                $productImage->delete();
            }
            if ($request->file('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('postCategories');
                    $image->move($destinationPath, $imageName);
                    PostProductImage::create([
                        "image" => 'public/postCategories/' . $imageName,
                        "post_product_id"=>$product->id
                    ]);
                }
            }
            DB::commit();
            return customApiResponse(true,[],"Product Updated Successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }  
    }
    public function delete(Request $request,$guid)
    {
        try {
            $user = $request->user();
            $product=PostProduct::where('guid',$guid)->where('user_id',$user->id)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            DB::beginTransaction();
            PostProductFeature::where('post_product_id',$product->id)->delete();
            $deleted_files=PostProductImage::where('post_product_id',$product->id)->get();
            foreach ($deleted_files as $file) {
                unlink($file->image);
                $file->delete();
            }
            $product->delete();
            DB::commit();
            return customApiResponse(true,[],"Product Deleted Successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function updateStatus(Request $request,$guid)
    {
        try {
            $user = $request->user();
            $product=PostProduct::where('guid',$guid)->where('user_id',$user->id)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            DB::beginTransaction();
            $statusValue=$product->status==1?0:1;
            $product->update([
                "status"=>$statusValue
            ]);
            if($statusValue==0){
                Favourite::where('favourite_against_id',$product->guid)->delete();
            }
            DB::commit();
            return customApiResponse(true,[],"Product Status Updated Successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function promote(Request $request,$guid)
    {
        try {
            $user = $request->user();
            $product=PostProduct::where('guid',$guid)->where('user_id',$user->id)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            $setting=AdminSetting::first();
            $days=$setting->promoted_days??5;
            DB::beginTransaction();
            $product->update([
                "promoted"=>1,
                "promoted_date"=>Carbon::now()->addDays($days)->format("Y-m-d")
            ]);
            $price=$setting->promoted_price??2;
            PostTransaction::create([
                "user_id"=>$user->id,
                "product_id"=>$product->id,
                "price"=>$price,
                "start_date"=>Carbon::now()->format('Y-m-d'),
                "end_date"=>Carbon::now()->addDays($days)->format("Y-m-d")
            ]);
            DB::commit();
            return customApiResponse(true,[],"Product Promoted Successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getCategoryWiseProducts(Request $request,$guid)
    {
        try {
            $category=PostCategory::where('status',1)->where('guid',$guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Category Not Found!', 404);
            }
            $productsQuery = PostProduct::with('category','extra','images','user','user.media')->where('status',1)->where('post_category_id',$category->id);
            $total = $productsQuery->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $products=$productsQuery->orderBy('promoted','desc')->orderBy('created_at','desc')->skip($skip)->take($page_size)->get();
            return customApiResponse(true,["products"=>$products,"pagination"=>$pagination],"Category Wise Products!");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
    public function storeFavourite(Request $request)
    {
        try {
        $user = $request->user();
        $input = $request->all();
        $rules = [
            'favourite_against_id'=>'required'  
        ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $product=PostProduct::where('guid',$request->favourite_against_id)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            DB::beginTransaction();
            $message = '';
            $fav = Favourite::where([
                ['user_id', $user->id],
                ['favourite_against_id', $request->favourite_against_id],
                ['type', 3]
            ])->first();
            if ($fav) {
                $fav->delete();
                $message = 'Favourite Successfully Removed!';
            } else {
                $fav = new Favourite;
                $fav->user_id = $user->id;
                $fav->favourite_against_id = $request->favourite_against_id;
                $fav->type = 3;
                if ($fav->save()) {
                    $message = 'Favourite Successfully Saved.';
                }
            }
            $is_favourite = Favourite::isFavourite($request->favourite_against_id, 3, $user->id);
            $total_favourite = Favourite::favouriteCount($request->favourite_against_id, 3);
            DB::commit();
            return customApiResponse(true,["is_favourite"=>$is_favourite,"total_favourite"=>$total_favourite],$message);
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getFavourites(Request $request)
    {
        try {
        $user = $request->user();
        $favourites=Favourite::where('user_id',$user->id)->where('type',3)->pluck('favourite_against_id')->toArray();
        $productsQuery = PostProduct::with('category','extra','images','user','user.media')->where('status',1)->whereIn('guid',$favourites);
        $total = $productsQuery->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $products=$productsQuery->orderBy('promoted','desc')->orderBy('created_at','desc')->skip($skip)->take($page_size)->get();       
            return customApiResponse(true,["favourites"=>$products,"pagination"=>$pagination],'Favourites');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function transaction(Request $request)
    {
        try {
            $user = $request->user();
            $total = PostTransaction::count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $products=PostTransaction::with('product','product.category','product.extra','product.images','user','user.media')->skip($skip)->take($page_size)->get();
            return customApiResponse(true,["transactions"=>$products,"pagination"=>$pagination],"Products");
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }   
    }
}