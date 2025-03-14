<?php

namespace App\Http\Controllers\Api;

use Str;
use App\Events\OfferMade;
use App\Helpers\StripeHelper;
use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brands;
use App\Models\SearchHistoryProduct;
use App\Models\Media;
use App\Models\Stock;
use App\Models\FeedBack;
use App\Models\InStock;
use App\Models\OutStock;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Service;
use App\Models\SellerData;
use App\Models\ProductsAttribute;
use App\Models\ProductAttributes;
use App\Models\User;
use App\Models\DeliverCompany;
use App\Models\SaveSearch;
use App\Models\RecentUserView;
use App\Models\Countries;
use App\Models\City;
use App\Models\State;
use App\Models\Fedex;
use App\Models\CategoryAttributes;
use App\Models\Attribute;
use App\Models\Message;
use App\Models\Order;
use App\Models\RecentView;
use App\Models\ProductRatings;
use App\Models\SavedUsersProduct;
use App\Models\ProductShippingDetail;
use App\Models\ShippingSize;
use App\Models\UserOfferProduct;
use App\Models\SaveAddress;
use App\Scopes\ActiveScope;
use App\Scopes\SoldScope;
use App\Models\Wishlist;
use App\Models\Favourite;
use App\Models\UserOrderDetails;
use App\Models\Bidding;
use App\Models\UserCart;
use App\Models\CheckOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfferMadeNotification;
use App\Notifications\AddReview;
use App\Notifications\AdApproved;
use App\Notifications\TrustedSeller;
use App\Notifications\DepositReminder;
use App\Images;
use Image;
use App\Models\Banner;

class OfferUser
{
    public $name;
    public $email;
    public $sender;
    public $price;
    public $product;
    public function routeNotificationFor()
    {
        return $this->email;
    }
}

class ProductController extends Controller
{
    
    public function deleteProduct($id)
    {
          try{
        $product=Product::find($id);
        $product->update([
            "is_deleted"=>1
        ]);
        UserCart::where('product_id',$id)->delete();
        CheckOut::where('product_id',$id)->delete();
        return response()->json(['status' => true,'message'=>"Product Deleted Successfully!", 'data' => []], 200);
          }
          catch(\Exception $e)
        {
            return response()->json(['status' => true, 'data' => [],"message"=>$e->getMessage()], 500);
        }
    }
    public function filterProductsUnderAge(Request $request)
    {
        try{
            
            $user_id = $request->user_id ?? null;
            
            $products=Product::where('is_sold',false)->where('is_deleted',0)->where('underage',0)->where('stockcapacity','>',0);
        if (!empty($user_id)  && $user_id!="undefined" && $user_id!="null" && $user_id !=null) {
            $products=$products->where('user_id','!=',$user_id);
            $user=User::find($user_id);
        }
       

             $productsArray=[];
             
                   if(isset($request->search_key) && !empty($request->search_key) && $request->search_key!=null && $request->search_key!="undefined" && $request->search_key!="null") {
              $products = $products->where('name','LIKE','%'.$request->search_key.'%');
          }
             
            
                          if(isset($request->auctioned) && !empty($request->auctioned) && $request->auctioned!=null && $request->auctioned!="undefined" && $request->auctioned!="null") {
              $products = $products->where('auctioned', $request->auctioned);
             
          }
          if(isset($request->category) && !empty($request->category) && $request->category!=null && $request->category!="undefined" && $request->category!="null") {
              $products = $products->where('category_id', $request->category);
          }
          if(isset($request->brand) && $request->brand != "all" && !empty($request->brand) && $request->brand!=null && $request->brand!="undefined" && $request->brand!="null") {
              $products = $products->where('brand_id', $request->brand);
          }
           if(isset($request->condition) && $request->condition != "all" && !empty($request->condition) && $request->condition!=null && $request->condition!="undefined" && $request->condition!="null") {
              $products = $products->where('condition', $request->condition);
          }
          if(isset($request->used_condition) && $request->used_condition != "all" && !empty($request->used_condition) && $request->used_condition!=null && $request->used_condition!="undefined" && $request->used_condition!="null") {
              $products = $products->where('used_condition', $request->used_condition);
            
          }
          if(isset($request->attributes)) {
              $test=json_encode($request->attributes);
               $attributes = json_decode($test, true);
                foreach ($attributes as $attribute) {
                    $key = $attribute['key'];
                    $value = $attribute['value'];
            
                    $products = $products->whereJsonContains('attributes', [['key' => $key, 'value' => $value]]);
                }
          }
          if (
            isset($request->min_price, $request->max_price) && 
            $request->min_price !== null && $request->min_price !== "undefined" && $request->min_price !== "null" && 
            $request->max_price !== null && $request->max_price !== "undefined" && $request->max_price !== "null"
        ) {
            $min = (int)$request->min_price;
            $max = (int)$request->max_price;
            $products = $products->where(function($query) use ($min, $max) {
                $query->where('auctioned', 0)->whereBetween('price', [$min, $max])
                      ->orWhere('auctioned', 1)->whereBetween('bids', [$min, $max]);
            });
        }
    
   
     $total = $products->count();
        $page = $request->page ?? 1;
        $page_size = $request->page_size ?? 16;
        if($page=="undefined")
    {
        $page=1;
    }
    if($page_size=="undefined")
    {
        $page_size=16;
    }
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
           $products = $products->skip($skip)->take($page_size)->get();
           
           
           foreach($products as $item)
    {
        $product = Product::where('id', $item->id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
            $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        array_push($productsArray,$product);
    }
    $data=[];
    foreach($products as $item){
        if($item->auctioned==1){
            if($item->auction_End_listing >= Carbon::now()->format('Y-m-d H:i:s')){
                $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage,
                     "condition"=>$item->condition,
                     "used_condition"=>$item->used_condition
                    
                ];
                $data[]=$arr;
            }
        }
        else{
            $arr=[
                "id"=>$item->id,
                "guid"=>$item->guid,
                "name"=>$item->name,
                "auctioned"=>$item->auctioned,
                "price"=>$item->price,
                "sale_price"=>$item->sale_price,
                "media"=>$item->media,
                 "bid_price"=>$item->bids,
                      "is_favourite"=>$item->is_favourite,
                 "favourite_count"=>$item->favourite_count,
                 "underage"=> $item->underage,
                 "condition"=>$item->condition,
                 "used_condition"=>$item->used_condition
                
            ];
            $data[]=$arr;
        }
        
    }
    
         return response()->json(['status' => true, 'data' => ["products"=>$data,"pagination"=>$pagination]], 200);
        }
         catch(\Exception $e)
        {
            return response()->json(['status' => true, 'data' => [],"message"=>$e->getMessage()], 500);
        }
    }
    public function filterProducts(Request $request)
    {
        try{
            
            $user_id = $request->user_id ?? null;
            
            $products=Product::where('is_sold',false)->where('is_deleted',0)->where('underage',1)->where('stockcapacity','>',0);
        if (!empty($user_id)  && $user_id!="undefined" && $user_id!="null" && $user_id !=null) {
            $products=$products->where('user_id','!=',$user_id);
            $user=User::find($user_id);
        }
       

             $productsArray=[];
             
                   if(isset($request->search_key) && !empty($request->search_key) && $request->search_key!=null && $request->search_key!="undefined" && $request->search_key!="null") {
              $products = $products->where('name','LIKE','%'.$request->search_key.'%');
          }
             
            
                          if(isset($request->auctioned) && !empty($request->auctioned) && $request->auctioned!=null && $request->auctioned!="undefined" && $request->auctioned!="null") {
              $products = $products->where('auctioned', $request->auctioned);
             
          }
          if(isset($request->category) && !empty($request->category) && $request->category!=null && $request->category!="undefined" && $request->category!="null") {
              $products = $products->where('category_id', $request->category);
          }
          if(isset($request->brand) && $request->brand != "all" && !empty($request->brand) && $request->brand!=null && $request->brand!="undefined" && $request->brand!="null") {
              $products = $products->where('brand_id', $request->brand);
          }
           if(isset($request->condition) && $request->condition != "all" && !empty($request->condition) && $request->condition!=null && $request->condition!="undefined" && $request->condition!="null") {
              $products = $products->where('condition', $request->condition);
          }
          if(isset($request->used_condition) && $request->used_condition != "all" && !empty($request->used_condition) && $request->used_condition!=null && $request->used_condition!="undefined" && $request->used_condition!="null") {
              $products = $products->where('used_condition', $request->used_condition);
            
          }
          if(isset($request->attributes)) {
              $test=json_encode($request->attributes);
               $attributes = json_decode($test, true);
                foreach ($attributes as $attribute) {
                    $key = $attribute['key'];
                    $value = $attribute['value'];
            
                    $products = $products->whereJsonContains('attributes', [['key' => $key, 'value' => $value]]);
                }
          }
          if (
            isset($request->min_price, $request->max_price) && 
            $request->min_price !== null && $request->min_price !== "undefined" && $request->min_price !== "null" && 
            $request->max_price !== null && $request->max_price !== "undefined" && $request->max_price !== "null"
        ) {
            $min = (int)$request->min_price;
            $max = (int)$request->max_price;
            $products = $products->where(function($query) use ($min, $max) {
                $query->where('auctioned', 0)->whereBetween('price', [$min, $max])
                      ->orWhere('auctioned', 1)->whereBetween('bids', [$min, $max]);
            });
        }
    
   
     $total = $products->count();
        $page = $request->page ?? 1;
        $page_size = $request->page_size ?? 16;
        if($page=="undefined")
    {
        $page=1;
    }
    if($page_size=="undefined")
    {
        $page_size=16;
    }
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
           $products = $products->skip($skip)->take($page_size)->get();
           
           
           foreach($products as $item)
    {
        $product = Product::where('id', $item->id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
            $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        array_push($productsArray,$product);
    }
    $data=[];
    foreach($products as $item){
        if($item->auctioned==1){
            if($item->auction_End_listing >= Carbon::now()->format('Y-m-d H:i:s')){
                $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage,
                     "condition"=>$item->condition,
                     "used_condition"=>$item->used_condition
                    
                ];
                $data[]=$arr;
            }
        }
        else{
            $arr=[
                "id"=>$item->id,
                "guid"=>$item->guid,
                "name"=>$item->name,
                "auctioned"=>$item->auctioned,
                "price"=>$item->price,
                "sale_price"=>$item->sale_price,
                "media"=>$item->media,
                 "bid_price"=>$item->bids,
                      "is_favourite"=>$item->is_favourite,
                 "favourite_count"=>$item->favourite_count,
                 "underage"=> $item->underage,
                 "condition"=>$item->condition,
                 "used_condition"=>$item->used_condition
                
            ];
            $data[]=$arr;
        }
        
    }
    
         return response()->json(['status' => true, 'data' => ["products"=>$data,"pagination"=>$pagination]], 200);
        }
         catch(\Exception $e)
        {
            return response()->json(['status' => true, 'data' => [],"message"=>$e->getMessage()], 500);
        }
    }
    
        public function maxPriceProduct(Request $request)
    {
        try{
           $maxPrice = Product::where('is_deleted',0)->max('price');
           $maxSalePrice= Product::where('is_deleted',0)->max('sale_price');
           $maxBidPrice= Product::where('is_deleted',0)->max('bids');
         return response()->json(['status' => true, 'data' => ["maxSalePrice"=>$maxSalePrice,"maxPrice"=>$maxPrice,"maxBidPrice"=>$maxBidPrice]], 200);
        }
         catch(\Exception $e)
        {
            return response()->json(['status' => true, 'data' => [],"message"=>$e->getMessage()], 500);
        }
    }
    
    public function getRelatedProducts($productID,Request $request)
    {
        try{
           
            $prod=Product::find($productID);
            $products=Product::where('category_id',$prod->category_id)->where('underage',$prod->underage)->where('auctioned',0)->where('is_deleted',0)->whereNotIn('id',[$prod->id])->pluck('id')->toArray();
            $productsArray=[];
            if(isset($request->user_id)){
                 $products=Product::where('category_id',$prod->category_id)->where('auctioned',0)->where('underage',$prod->underage)->whereNotIn('id',[$prod->id])->where('user_id', '!=', $request->user_id)->pluck('id')->toArray();
            }
             

    $data=[];
    foreach($products as $item)
    {
        $product = Product::where('id', $item)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
         $arr=[
                    "id"=>$product->id,
                    "guid"=>$product->guid,
                    "name"=>$product->name,
                    "auctioned"=>$product->auctioned,
                    "price"=>$product->price,
                    "sale_price"=>$product->sale_price,
                    "media"=>$product->media,
                     "bid_price"=>$product->bids,
                          "is_favourite"=>$product->is_favourite,
                     "favourite_count"=>$product->favourite_count,
                     "underage"=> $product->underage
                    
                ];
                $data[]=$arr;
    }
    
         return response()->json(['status' => true, 'data' => ["products"=>$data]], 200);
        }
        catch(\Exception $e)
        {
            return response()->json(['status' => true, 'data' => [],"message"=>$e->getMessage()], 500);
        }
        
    }
    public function getTopSellers(Request $request)
    {
        $topSellingProducts = DB::table('tbl_user_order_details')
    ->select('product_id')
    ->selectRaw('COUNT(product_id) as count')
    ->groupBy('product_id')
    ->orderByDesc('count')
    ->take(50)
    ->get();
    $sellerIds=[];
    $sellers=[];
     foreach( $topSellingProducts as $item)
    {
        $product = Product::where('id', $item->product_id)->first();
        array_push($sellerIds,$product->user_id);
    }
    $sellerIds = array_unique($sellerIds);
    $loggedInUserId = $request->user_id??0;
$sellerIds = array_diff($sellerIds, [$loggedInUserId]);
$sellerIds = array_values($sellerIds);
foreach($sellerIds as $id)
{    $sellerData = SellerData::with('feedback')->where('user_id', $id)->first();
        $sellerDataCount = FeedBack::where('store_id', $sellerData->id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $sellerData->id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
            'seller_guid'=>$sellerData->guid
        ];
        array_push($sellers,$sellerData_);
}
     return response()->json(['status' => true, 'data' =>["top_sellers"=>$sellers]], 200);
    }
    public function getHotUnderAge(Request $request)
    {
           $id=0;
        if(isset($request->user_id) && $request->user_id!='undefined' && !empty($request->user_id))
        {
            $id=$request->user_id;
        }
        $hot=Product::where('hot',1)->where('underage',0)->where('is_sold',false)->where('is_deleted',0)->get();
    $hotFinal=[];
    foreach( $hot as $item)
    {
        
        $product = Product::where('id', $item->id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            ->first();
                    if($product->auctioned==1){
                          $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
                    }
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
                   $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        $item=$product;
    }
      foreach( $hot as $item)
    {
       
        if($item->user_id!=$id){
            array_push($hotFinal,$item);
        }
    }
    $dataHot=[];
     foreach($hotFinal as $item)
    {
           $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage
                    
                ];
                $dataHot[]=$arr;
    }
    $total=count($dataHot);
    $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            if($page=="undefined")
    {
        $page=1;
    }
    if($page_size=="undefined")
    {
        $page_size=10;
    }
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
            $test=collect($dataHot)->skip($skip)->take($page_size)->all();
            return response()->json(['status' => true, 'data' => ["products"=>$test,"pagination"=>$pagination]], 200);
    }
    
    public function getTopSellingUnderAge(Request $request)
    {
           $id=0;
        if(isset($request->user_id) && $request->user_id!='undefined' && !empty($request->user_id))
        {
            $id=$request->user_id;
        }
     $topSellingProducts = DB::table('tbl_user_order_details')
    ->join('products', 'tbl_user_order_details.product_id', '=', 'products.id')
    ->select('tbl_user_order_details.product_id')
    ->selectRaw('COUNT(tbl_user_order_details.product_id) as count')
    ->where('products.underage', 0)
    ->where('products.auctioned', 0)
    ->groupBy('tbl_user_order_details.product_id')
    ->orderByDesc('count')
    ->take(10)
    ->get();
    $topSellingProductsFinal=[];
    foreach( $topSellingProducts as $item)
    {
         $product = Product::where('id', $item->product_id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
          
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
                 $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        $item->product=$product;
    }
    foreach( $topSellingProducts as $item)
    {
        if($item->product->user_id!=$id && $item->product->is_deleted==0){
            array_push($topSellingProductsFinal,$item->product);
        }
    }
    $data=[];
     foreach($topSellingProductsFinal as $item)
    {
           $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage
                    
                ];
                $data[]=$arr;
    }
    return response()->json(['status' => true, 'data' => ["products"=> $data]], 200);
    }
    public function getUnderageBanners()
    {
        $now=Carbon::now();
          $banners=array();
          $featuredBanners=array();
          $getBanners=Banner::with('media')->where('active',1)->where('featured',0)->where('underage',1)->get();
          $getFeaturedBanners=Banner::with('media')->where('active',1)->where('featured',1)->where('underage',1)->where('featured_until','>',$now)->get();
          foreach ($getBanners as $value) {
            $imageUrl=url('/').'/image/category/'.$value->media[0]->name;
            array_push($banners,["image"=>$imageUrl]);
          }
          foreach ($getFeaturedBanners as $value) {
            $value->imageUrl=url('/').'/image/category/'.$value->media[0]->name;
            array_push($featuredBanners,$value);
          }
     return response()->json(['status' => true, 'data' =>["banners"=>$banners,"featuredBanners"=>$featuredBanners]], 200);
    }
    public function getTopSelling(Request $request)
    {
         $id=0;
        if(isset($request->user_id) && $request->user_id!='undefined' && !empty($request->user_id))
        {
            $id=$request->user_id;
        }
     $topSellingProducts = DB::table('tbl_user_order_details')
    ->join('products', 'tbl_user_order_details.product_id', '=', 'products.id')
    ->select('tbl_user_order_details.product_id')
    ->selectRaw('COUNT(tbl_user_order_details.product_id) as count')
    ->where('products.underage', 1)
    ->where('products.auctioned', 0)
    ->where('products.is_sold', false)
    ->groupBy('tbl_user_order_details.product_id')
    ->orderByDesc('count')
    ->take(10)
    ->get();
    
    
    $now=Carbon::now();
          $banners=array();
          $featuredBanners=array();
          $getBanners=Banner::with('media')->where('active',1)->where('featured',0)->where('underage',0)->get();
          $getFeaturedBanners=Banner::with('media')->where('active',1)->where('featured',1)->where('underage',0)->where('featured_until','>',$now)->get();
          foreach ($getBanners as $value) {
            $imageUrl=url('/').'/image/category/'.$value->media[0]->name;
            array_push($banners,["image"=>$imageUrl]);
          }
          foreach ($getFeaturedBanners as $value) {
            $value->imageUrl=url('/').'/image/category/'.$value->media[0]->name;
            array_push($featuredBanners,$value);
          }
     $topSellingProductsFinal=[];
    foreach( $topSellingProducts as $item)
    {
         $product = Product::where('id', $item->product_id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
          
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
                 $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        $item->product=$product;
    }
    $hot=Product::where('hot',1)->where('underage',1)->where('is_sold',false)->get();
    $hotFinal=[];
    foreach( $hot as $item)
    {
        
        $product = Product::where('id', $item->id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            ->first();
                    if($product->auctioned==1){
                          $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
                    }
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
                   $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        $item=$product;
    }
    foreach( $topSellingProducts as $item)
    {
        if($item->product->user_id!=$id && $item->product->is_deleted==0){
            array_push($topSellingProductsFinal,$item);
        }
        
        
    }
     $data=[];
    foreach($topSellingProductsFinal as $item)
    {
           $arr=[
                    "id"=>$item->product->id,
                    "guid"=>$item->product->guid,
                    "name"=>$item->product->name,
                    "auctioned"=>$item->product->auctioned,
                    "price"=>$item->product->price,
                    "sale_price"=>$item->product->sale_price,
                    "media"=>$item->product->media,
                     "bid_price"=>$item->product->bids,
                          "is_favourite"=>$item->product->is_favourite,
                     "favourite_count"=>$item->product->favourite_count,
                     "underage"=> $item->product->underage
                    
                ];
                $data[]=$arr;
    }
    foreach( $hot as $item)
    {
       
        if($item->user_id!=$id && $item->is_deleted==0){
            array_push($hotFinal,$item);
        }
    }
     $dataHot=[];
    foreach($hotFinal as $item)
    {
           $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage
                    
                ];
                $dataHot[]=$arr;
    }
    return response()->json(['status' => true, 'data' =>["featuredBanners"=>$featuredBanners,"banners"=>$banners,"products"=> $data,"hot"=>$dataHot]], 200);

    }
   
    public function index(Request $request)
    {
        $user_id = $request->user_id ?? null;

        if (!empty($user_id) && $user_id!="undefined" && $user_id!="null" && $user_id!=null) {
            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                ->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['category'])
                ->with(['brand'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('products.is_deleted', 0)
                ->where('user_id', '!=', $user_id)
                ->where('products.stockcapacity', '>', 0)
                ->where('auctioned', 0)
                ->where('products.underage', 1)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productNormal as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                $product->auction_remainig_time=0;
            }
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                //->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['brand'])
                ->with(['category'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('user_id', '!=', $user_id)
                ->where('products.auctioned', 1)
                ->where('products.underage', 1)
                ->where('products.is_deleted', 0)
                ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                // ->where('products.IsSaved', true)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productAuctioned as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                       $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            }
            $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
            if ($product) {
                $data=[];
            foreach($product as $pro){
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage
                    
                ];
                $data[]=$arr;
            }
                return response()->json(['status' => true, 'data' => $data], 200);
            } else {
                return response()->json(['status' => false, 'data' => [],"message"=>"Unable to get products!"], 200);
            }
        } else {
            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                ->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['category'])
                ->with(['brand'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('products.is_deleted', 0)
                ->where('products.stockcapacity', '>', 0)
                ->where('products.auctioned', 0)
                  ->where('products.underage', 1)
                
                // ->where('products.IsSaved', true)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productNormal as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                $product->auction_remainig_time=0;
            }
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                //->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['brand'])
                ->with(['category'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('products.auctioned', 1)
                ->where('products.is_deleted', 0)
                ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                  ->where('products.underage', 1)
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productAuctioned as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                           // $attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                      $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            }
            $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
            $data=[];
            foreach($product as $pro){
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage
                    
                ];
                $data[]=$arr;
            }
            if ($product) {
                return response()->json(['status' => true, 'data' => $data], 200);
            } else {
                return response()->json(['status' => false, 'data'=>[],"message" => "Unable To Get Products"], 200);
            }
        }

    }
    
    public function getUnderAgeProducts(Request $request)
    {
        $user_id = $request->user_id ?? null;
        
         $page = $request->page ?? 1;
    $page_size = $request->page_size ?? 10;
    if($page=="undefined")
    {
        $page=1;
    }
    if($page_size=="undefined")
    {
        $page_size=10;
    }
    $skip = $page_size * ($page - 1);
   

            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                ->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['category'])
                ->with(['brand'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where('products.is_sold', false)
                ->where('user_id', '!=', $user_id)
                ->where('products.is_deleted', 0)
                ->where('products.stockcapacity', '>', 0)
                ->where('auctioned', 0)
                ->where('products.underage', 0)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productNormal as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                $product->auction_remainig_time=0;
            }
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                //->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['brand'])
                ->with(['category'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where('products.is_sold', false)
                ->where('user_id', '!=', $user_id)
                ->where('products.auctioned', 1)
                ->where('products.underage', 0)
                ->where('products.is_deleted', 0)
                ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                // ->where('products.IsSaved', true)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productAuctioned as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                       $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            }
               $totalNormal = $productNormal->count();
    $totalAuctioned = $productAuctioned->count();
    $total = $totalNormal + $totalAuctioned;
    
     $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            if($page=="undefined")
            {
                $page=1;
            }
            if($page_size=="undefined")
            {
                $page_size=10;
            }
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
            $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
            $test=collect($product)->skip($skip)->take($page_size)->all();
            $data=[];
            foreach($test as $pro){
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage
                    
                ];
                $data[]=$arr;
            }
            if ($product) {
                return response()->json(['status' => true, 'data' => ["products"=>$data,"pagination"=>$pagination]], 200);
            } else {
                return response()->json(['status' => false, 'data' =>[],"message"=> "Unable To Get Products"], 200);
            }
        
    }
    
       public function getCategoryWiseProduct($categoryId,Request $request)
    {
         $user_id = $request->user_id ?? null;
         $underage=$request->underage??1;
      
          if (!empty($user_id)  && $user_id!="undefined" && $user_id!="null" && $user_id !=null) 
        {
            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            ->where('products.price', '<>', null)
            ->with(['user'])
            ->with(['category'])
            ->with(['brand'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('user_id', '!=', $user_id)
            ->where('products.is_deleted', 0)
            ->where('products.category_id', $categoryId)
            ->where('products.stockcapacity', '>', 0)
            ->where('auctioned', 0)
            ->where('products.underage', $underage)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productNormal as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
            $product->auction_remainig_time=0;
        }
        $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            //->where('products.price', '<>', null)
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('user_id', '!=', $user_id)
            ->where('products.auctioned', 1)
            ->where('products.underage', $underage)
            ->where('products.category_id', $categoryId)
            ->where('products.is_deleted', 0)
            ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            // ->where('products.IsSaved', true)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            // ->paginate($this->pageSize, [
            //     'categories.name as category',
            //     'products.*'
            // ]);
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productAuctioned as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                 $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
        if ($product) {
            $data=[];
            foreach($product as $item){
             $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage
                    
                ];
                $data[]=$arr;
            }
            return response()->json(['status' => true, 'data' => $data], 200);
        } else {
            return response()->json(['status' => false, 'data' =>[],"message"=> "Unable To Get Products"], 200);
        }
        }
        else{
            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            ->where('products.price', '<>', null)
            ->with(['user'])
            ->with(['category'])
            ->with(['brand'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('products.category_id', $categoryId)
            ->where('products.stockcapacity', '>', 0)
            ->where('auctioned', 0)
            ->where('products.underage', $underage)
            ->where('products.is_deleted', 0)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productNormal as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
            $product->auction_remainig_time=0;
        }
        $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            //->where('products.price', '<>', null)
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('products.auctioned', 1)
            ->where('products.underage', $underage)
            ->where('products.is_deleted', 0)
            ->where('products.category_id', $categoryId)
            ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            // ->where('products.IsSaved', true)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            // ->paginate($this->pageSize, [
            //     'categories.name as category',
            //     'products.*'
            // ]);
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productAuctioned as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                    $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
        if ($product) {
            $data=[];
            foreach($product as $item){
             $arr=[
                    "id"=>$item->id,
                    "guid"=>$item->guid,
                    "name"=>$item->name,
                    "auctioned"=>$item->auctioned,
                    "price"=>$item->price,
                    "sale_price"=>$item->sale_price,
                    "media"=>$item->media,
                     "bid_price"=>$item->bids,
                          "is_favourite"=>$item->is_favourite,
                     "favourite_count"=>$item->favourite_count,
                     "underage"=> $item->underage
                    
                ];
                $data[]=$arr;
            }
            return response()->json(['status' => true, 'data' => $data], 200);
        } else {
            return response()->json(['status' => false, 'data' =>[],"message"=> "Unable To Get Products"], 200);
        }
        }
    }
    
        public function getAuctionedProductsForUser(Request $request)
    {
        $user_id = $request->user_id ?? null;
        $underage=$request->underage??1;
        

        if(!empty($user_id) && $user_id!=null && $user_id !="null" && $user_id!="undefined")
        {
            $productLatest = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
           
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where('products.is_sold', false)
            ->where('user_id', '!=', $user_id)
            ->where('products.auctioned', 1)
            ->where('products.underage',$underage)
            ->where('products.is_deleted', 0)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            ->limit(4)
            ->get([
                'categories.name as category',
                'products.*'
            ]);
           
        foreach ($productLatest as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                   $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
          
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        }
        
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where('products.is_sold', false)
            ->where('user_id', '!=', $user_id)
            ->where('products.auctioned', 1)
            ->where('products.is_deleted', 0)
            ->where('products.underage',$underage)
            //->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d'))
            // ->where('products.IsSaved', true)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            // ->paginate($this->pageSize, [
            //     'categories.name as category',
            //     'products.*'
            // ]);
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productAuctioned as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
          
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        }
        $data=[];
            foreach($productLatest as $pro)
            {
                if(Carbon::parse($pro->auction_End_listing) >= Carbon::now()->format('Y-m-d')){
                    $arr=[
                        "id"=>$pro->id,
                        "guid"=>$pro->guid,
                        "name"=>$pro->name,
                        "auctioned"=>$pro->auctioned,
                        "price"=>$pro->price,
                        "sale_price"=>$pro->sale_price,
                        "media"=>$pro->media,
                         "bid_price"=>$pro->bids,
                              "is_favourite"=>$pro->is_favourite,
                         "favourite_count"=>$pro->favourite_count,
                         "underage"=> $pro->underage,
                        "auction_remainig_time"=>$pro->auction_remainig_time,
                        "total_bids"=>$pro->total_bids
                    ];
                    $data[]=$arr;
                }
                
            }
            
            $dataAuction=[];
            foreach($productAuctioned as $pro)
            {
                if(Carbon::parse($pro->auction_End_listing) >= Carbon::now()->format('Y-m-d')){
                    $arr=[
                        "id"=>$pro->id,
                        "guid"=>$pro->guid,
                        "name"=>$pro->name,
                        "auctioned"=>$pro->auctioned,
                        "price"=>$pro->price,
                        "sale_price"=>$pro->sale_price,
                        "media"=>$pro->media,
                         "bid_price"=>$pro->bids,
                              "is_favourite"=>$pro->is_favourite,
                         "favourite_count"=>$pro->favourite_count,
                         "underage"=> $pro->underage,
                        "auction_remainig_time"=>$pro->auction_remainig_time,
                        "total_bids"=>$pro->total_bids
                    ];
                    $dataAuction[]=$arr;
                }
            }
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total=count($dataAuction);
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
        $auctionP=collect($dataAuction)->skip($skip)->take($page_size)->values()->toArray();
        return response()->json(['status' => true, 'data' => ["latest"=>$data,"auctioned"=>$auctionP,"pagination"=>$pagination]], 200);
        }
        else{
            $productLatest = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('user_id', '!=', $user_id)
            ->where('products.auctioned', 1)
            ->where('products.is_deleted', 0)
            ->where('products.underage',$underage)
            ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            // ->where('products.IsSaved', true)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            ->limit(4)
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productLatest as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                    $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
          
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        }
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            // ->where('products.weight', '<>', null)
            ->with(['user'])
            ->with(['brand'])
            ->with(['category'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['shop'])
            ->where($this->applyFilters($request))
            ->where('products.is_sold', false)
            ->where('products.auctioned', 1)
            ->where('products.is_deleted', 0)
            ->where('products.underage',$underage)
            ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            // ->where('products.IsSaved', true)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            // ->paginate($this->pageSize, [
            //     'categories.name as category',
            //     'products.*'
            // ]);
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        foreach ($productAuctioned as $product) {
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
                   $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
          
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        }
        $data=[];
            foreach($productLatest as $pro)
            {
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage,
                     "auction_remainig_time"=>$pro->auction_remainig_time,
                     "total_bids"=>$pro->total_bids
                    
                ];
                $data[]=$arr;
            }
            $dataAuction=[];
            foreach($productAuctioned as $pro)
            {
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage,
                     "auction_remainig_time"=>$pro->auction_remainig_time,
                    "total_bids"=>$pro->total_bids
                ];
                $dataAuction[]=$arr;
            }
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total=count($dataAuction);
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
           $auctionP=collect($dataAuction)->skip($skip)->take($page_size)->values()->toArray();
        return response()->json(['status' => true, 'data' => ["latest"=>$data,"auctioned"=>$auctionP,"pagination"=>$pagination]], 200);
        }
    }

    public function recentView(Request $request)
    {
        // $recentProducts = RecentView::with(['products'])
        // ->orderBy('created_at', 'DESC')->get();
        // $userProducts = [];
        // foreach($recentProducts as $recentProduct){
        //     //    $getRecent =  
        //     array_push($userProducts, $recentProduct);
        // }
        // return $userProducts;
        $recentProducts = RecentView::with(['products'])
            ->join('products', 'recent_view.product_id', '=', 'products.id')
            ->where('products.active', true)
            ->orderBy('recent_view.created_at', 'DESC')->get();
        $userProducts = [];
        foreach ($recentProducts as $recentProduct) {
            //    $getRecent =  
            array_push($userProducts, $recentProduct);
        }
        return $userProducts;
    }
    public function recentUserView(Request $request)
    {
        $userProduct = RecentUserView::where('user_id', \Auth::user()->id)
            ->with(['user'])
            ->with(['recent'])
            ->with(['product'])
            ->get();
        // $recent =  RecentView::with(['products'])->orderBy('created_at', 'DESC')->get();
        // $products = [];
        // foreach($recent as $rcent){
        //     array_push($products, $rcent->products);
        // }
        $product = [];
        foreach ($userProduct as $pro) {
            // if($pro->user_id == \Auth::user()->id){
            array_push($product, $pro->product);
            // }
            //   
        }
        if ($product) {
            return response()->json(['status' => 'true', 'data' => $product], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => $product], 500);
        }
    }
    public function inStock(Request $request)
    {
        $stockIn = InStock::with('products')
            ->where('user_id', \Auth::user()->id)->get();

        if ($stockIn) {
            return response()->json(['status' => 'true', 'data' => $stockIn], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Get Inventory!'], 500);
        }
    }
    public function outStock(Request $request)
    {
        $stockOut = OutStock::with('products')
            ->where('user_id', \Auth::user()->id)->get();
        if ($stockOut) {
            return response()->json(['status' => 'true', 'data' => $stockOut], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Get Inventory!'], 500);
        }

    }

    public function deleteRecent(Request $request)
    {
        $delete = RecentUserView::where('user_id', \Auth::user()->id)->delete();
        if ($delete) {
            return response()->json(['status' => 'true', 'data' => "Recent view has been CLeared!"], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to CLeared Recent view!'], 500);
        }
    }

    public function createUserRecientView(Request $request)
    {
        $product = Product::where('guid', $request->get('id'))->first();
        $recentview = RecentView::orderby('id', 'desc')->first();
        $recentuserview = new RecentUserView();
        $recentuserview->recent_view_id = $recentview->id;
        $recentuserview->product_id = $product->id;
        $recentuserview->user_id = \Auth::user()->id;
        $recentuserview->save();
    }


    public function createRecentView(Request $request)
    {
        DB::beginTransaction();
        try {
            $product = Product::where('guid', $request->get('id'))->first();

            RecentView::where('product_id', $product->id)->delete();
            // RecentView::with(['products'])->orderBy('created_at', 'DESC')->get();
            $recentview = new RecentView();
            $recentview->product_id = $product->id;
            $recentview->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
    public function checkEmailReview($guid)
    {
        // $user = User::where('id',$userID)->first();
        // $user->notify(new AddReview($user));

        $product = Product::where('guid', $guid)->first();

        if ($product->in_review == false) {
            $user = User::where('id', $product->user_id)->first();
            if ($user->is_autoAdd) {
                return "Your ad has been posted successfully";
            } else {
                return "Your Add has been Sent for Approval!";
            }

        }
    }
    public function self()
    {
        // return Product::where('user_id', \Auth::user()->id)
        //     ->with(['category', 'media'])
        //     ->withoutGlobalScope(ActiveScope::class)
        //     ->withoutGlobalScope(SoldScope::class)
        //     ->paginate($this->pageSize);
        return Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->with(['media'])
            ->with(['savedUsers'])
            ->with(['user'])
            ->with(['shop'])
            ->where('user_id', \Auth::user()->id)
            // ->where('products.weight', '<>', null)
            // ->where($this->applyFilters($request))
            // ->where('products.is_sold', false)
            ->withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)
            ->orderByDesc('products.featured')
            ->orderByDesc('products.created_at')
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        // ->paginate($this->pageSize, [
        //     'categories.name as category',
        //     'products.*'
        // ]);
    }
    public function selfItems(Request $request, $status)
    {
        // return Product::where('user_id', \Auth::user()->id)
        //     ->with(['category', 'media'])
        //     ->withoutGlobalScope(ActiveScope::class)
        //     ->withoutGlobalScope(SoldScope::class)
        //     ->paginate($this->pageSize);
        if ($status == "active") {
            $product = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['user'])
                ->where('user_id', \Auth::user()->id)
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                // ->where($this->applyFilters($request))
                // ->where('products.is_sold', false)
                ->withoutGlobalScope(ActiveScope::class)
                ->withoutGlobalScope(SoldScope::class)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get(
                    [
                        'categories.name as category',
                        'products.*'
                    ]
                );
            if ($product) {
                return response()->json(['status' => 'true', 'data' => $product], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Create Product!'], 403);
            }
        } else if ($status == "inactive") {
            // return Product::join('categories as categories','categories.id','=','products.category_id')
            $product = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['user'])
                ->where('user_id', \Auth::user()->id)
                ->where('products.active', false)
                // ->where('products.weight', '<>', null)
                // ->where($this->applyFilters($request))
                // ->where('products.is_sold', false)
                ->withoutGlobalScope(ActiveScope::class)
                ->withoutGlobalScope(SoldScope::class)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get(
                    [
                        'categories.name as category',
                        'products.*'
                    ]
                );

            if ($product) {
                return response()->json(['status' => 'true', 'data' => $product], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Create Product!'], 403);
            }
        } else if ($status == "scheduled") {
            // return Product::join('categories as categories','categories.id','=','products.category_id')
            // ->with(['media'])
            // ->with(['savedUsers'])
            // ->with(['user'])
            // ->where('scheduled', true)
            // ->where('user_id', \Auth::user()->id)
            // // ->where('products.weight', '<>', null)
            // // ->where($this->applyFilters($request))
            // // ->where('products.is_sold', false)
            // ->withoutGlobalScope(ActiveScope::class)
            // ->withoutGlobalScope(SoldScope::class)
            // ->orderByDesc('products.featured')
            // ->orderByDesc('products.created_at')
            // // ->paginate($this->pageSize, [
            // //     'categories.name as category',
            // //     'products.*'
            // // ]);
            // ->get(
            //     [
            //     'categories.name as category',
            //     'products.*'
            //     ]
            // );
            $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                ->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('auctioned', 0)
                // ->orwhere('products.auction_End_listing' ,'>=', today())
                // ->where('products.IsSaved', true)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
                foreach ($productNormal as $product) {
                    $test = json_decode(json_decode($product->attributes, true), true);
                    $attributes = [];
                    if (isset($test)) {
                        foreach ($test as $te) {
                            if (!isset($attributes[$te['key']])) {
                               // $attributes[$te['key']] = [];
                            }
                            $data = [
                                "key"=>$te['key'],
                               "options" => $te['value']
                            ];
                            $attributes[] = $data;
                        }
                    } else {
                        $attributes = [];
                    }
                    $product->attributes = $attributes;
                    $product->auction_remainig_time=0;
                }
            $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                //->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('products.auction_listing', '<>', null)
                ->orwhere('products.auction_listing', '>=', today())
                ->where('products.auctioned', 1)
                ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productAuctioned as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                      $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            }
            $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
            if ($product) {
                return response()->json(['status' => 'true', 'data' => $product], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Create Product!'], 403);
            }
        } else {
            $product = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['user'])
                ->where('user_id', \Auth::user()->id)
                // ->where('products.weight', '<>', null)
                // ->where($this->applyFilters($request))
                // ->where('products.is_sold', false)
                ->withoutGlobalScope(ActiveScope::class)
                ->withoutGlobalScope(SoldScope::class)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                // ->paginate($this->pageSize, [
                //     'categories.name as category',
                //     'products.*'
                // ]);
                ->get(
                    [
                        'categories.name as category',
                        'products.*'
                    ]
                );
            if ($product) {
                return response()->json(['status' => 'true', 'data' => $product], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Create Product!'], 403);
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        if($input['auctioned']==0)
        {
            if (isset($input['saleprice']) && !empty($input['saleprice'])  && $input['saleprice']!="undefined" && $input['saleprice']!="null" && $input['saleprice'] !=null && $input['saleprice'] >= $input['price']) {
                return response()->json(['status' => false, 'message' => "Discount price must be less than the actual price", 'data' => []], 400);
            }
        }
       
        return DB::transaction(function () use (&$request) {
            $getBrand=Brands::where('name','=',$request->brands)->first();
        $brandID=null;
        if($getBrand){
            $brandID=$getBrand->id;
        }
        else{
            if(!empty($request->brands))
                {
                    $brand= Brands::create([
                        "name"=>$request->brands,
                        "guid"=>Str::uuid()
                    ]);
                    $brandID=$brand->id;
                }
        }
            $active = false;
            $product = new Product();
            $user = User::where('id', Auth::user()->id)->first();
           
            $store = SellerData::where('user_id', Auth::user()->id)->first();
            $product->user_id = Auth::user()->id;
            $product->condition = $request->get('condition');
            $product->terms_descriptions = $request->get('termsdescription');
            $product->name = $request->get('title');
            $product->category_id = $request->get('category');
            $product->brand_id = $brandID;
            $product->model = $request->get('model');
            $product->description = $request->get('description');
            $product->tags = json_encode($request->get('tags'));
            if ($request->get('stockCapacity') > 1) {
                $product->recurring = true;
            } else if ($request->get('stockCapacity') == 1) {
                $product->recurring = false;
            }
            $product->stockcapacity = $request->get('stockCapacity');
            $product->attributes = json_encode($request->get('attributes'));
            $sellingNow = 0;
            if ($request->get('sellingNow') == 'true') {
                $sellingNow = 1;
               
            }
            $product->selling_now = $sellingNow;
            $product->price = $request->get('price');
            $product->sale_price = $request->get('saleprice') ? $request->get('saleprice') : 0;
            $product->min_purchase = $request->get('minpurchase');
            $auctioned = 0;
            if ($request->get('auctioned') == 'true') {
                $auctioned = 1;
                $product->bids = $request->get('bids');
                $product->auction_listing = $request->get('auctionListing');
                $product->auction_End_listing = Carbon::parse($request->get('end_listing'));
            }
            $product->auctioned = $auctioned;
            $product->durations = $request->get('durations');
            $product->hours = $request->get('hours');
            $deliverdDomestic = 0;
            if ($request->get('deliverddomestic') == 'true') {
                $deliverdDomestic = 1;
            }
            $product->deliverd_domestic = $deliverdDomestic;

            $deliverdInternational = 0;
            if ($request->get('deliverdinternational') == 'true') {
                $deliverdInternational = 1;
            }
            $product->deliverd_international = $deliverdInternational;
            $product->delivery_company = $request->get('deliverycompany');
            $product->is_sold = false;
            $product->postal_address = $request->get('address');
            $product->street_address = $request->get('address');
            $product->latitude = $request->get('latitude');
            $product->longitude = $request->get('longitude');
            $product->country = $request->get('country');
            $product->city = $request->get('city');
            $product->state = $request->get('state');
            $product->zip = $request->get('zip');
            $product->IsSaved = true;
            $product->shipping_price = $request->get('shippingprice');
            $product->shiping_durations = $request->get('shipingdurations');
           
            $product->return_shipping_price = $request->get('returnshippingprice');
            $product->return_ship_duration_limt = $request->get('returndurationlimit');
            $product->return_ship_paid_by = $request->get('returnshippingpaidby');
            // $product->return_ship_location = $request->get('returnshippinglocation');
            // $product->return_country = $request->get('returncountry');
            // $product->return_state = $request->get('returnstate');
            // $product->return_city = $request->get('returncity');
            // $product->return_zip = $request->get('returnzip');
            $product->return_ship_location = $store->address;//$request->get('returnshippinglocation');
            $product->return_country = $store->country_id;//$request->get('returncountry');
            $product->return_state = $store->state_id;//$request->get('returnstate');
            $product->return_city = $store->city_id;//$request->get('returncity');
            $product->return_zip = $store->zip;//$request->get('returnzip');
            $product->shop_id = $store->id;
            $product->active = true;
            $product->weight=$request->get('weight')??1;
            $product->underage=$request->get('underage')??1;
            $product->used_condition=$request->get('used_condition')??"";
            $product->height=$request->get('height')??1;
            $product->width=$request->get('width')??1;
            $product->length=$request->get('length')??1;
            $product->weight=$request->get('weight')??1;
            // $product->shop_id = $store->id;
            $product->save();
            //return $product;
            //die();
            /**
             * For Product Ends
             */

            /**
             * For Images Uploading Start
             */
            $imageName = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {

                    $extension = $file->getClientOriginalExtension();
                    $guid = GuidHelper::getGuid();
                    // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
                    $name = "{$guid}.{$extension}";
                    $path = 'images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $product->id . '/' . "{$guid}.{$extension}";
                    $pathName = env('APP_URL').'/images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $product->id . '/' . "{$guid}.{$extension}/" . "{$guid}.{$extension}";
                    $media = new Media();
                    // $name = 'images/'.Product::MEDIA_UPLOAD.'/'.$user->id.'/'. $product->id.'/'."{$guid}.{$extension}";
                    $properties = [
                        'name' => $pathName,
                        'extension' => $extension,
                        'type' => Product::MEDIA_UPLOAD,
                        'user_id' => Auth::user()->id,
                        'product_id' => $product->id,
                        'url' => $pathName,
                        'active' => true
                    ];

                    $media->fill($properties);
                    $media->save();
                    // $path = User::getUploadPath(Auth::user()->id) . StringHelper::trimLower(Media::PRODUCT_IMAGES);
                    $image = Image::make($file);
                    $image->orientate();
                    $image->resize(300, 300);
                    $image->stream();
                    $file->move($path, "{$guid}.{$extension}");
                }
            }

            // $imageName = [];
            // if($request->hasFile('file')){
            //     foreach ($request->file('file') as $file) {
            //         $extension = $file->getClientOriginalExtension();
            //         $guid = GuidHelper::getGuid();
            //         // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
            //         $name = "{$guid}.{$extension}";
            //         $path = env('APP_URL').'images/'.Product::MEDIA_UPLOAD.'/'.Auth::user()->id.'/'."{$guid}.{$extension}";

            //         $media = new Media();

            //         $properties = [
            //             'name' => $name,
            //             'extension' => $extension,
            //             'type' => Product::MEDIA_UPLOAD,
            //             'user_id' => Auth::user()->id,
            //             'product_id' => $product->id,
            //             'url' => $path,
            //             'active' => true,
            //         ];

            //         $media->fill($properties);
            //         $media->save();
            //         $path = 'images/'.Product::MEDIA_UPLOAD.'/'.Auth::user()->id;
            //         // $path = User::getUploadPath(Auth::user()->id) . StringHelper::trimLower(Media::PRODUCT_IMAGES);
            //         $image = Image::make($file);
            //         $image->orientate();
            //         $image->resize(1024, null, function ($constraint) {
            //             $constraint->aspectRatio();
            //             $constraint->upsize();
            //         });
            //         $image->stream();
            //         $file->move($path, "{$guid}.{$extension}");
            //     }
            // }

            /**
             * For Images Uploading End
             */

            /**
             * For Product Attributes Start
             */
            // $sizes = json_decode($request->get('sizes'));
            // foreach($sizes as $size){
            //     foreach($size as $key => $siz){
            //         $productattributes =new ProductAttributes();
            //         $productattributes->name=$key;
            //         $productattributes->value=$siz;
            //         $productattributes->product_id=$product->id;
            //         $productattributes->save();
            //     }
            // }

            /**
             * For Product Attributes Ends
             */

            /**
             * Stock Starts
             */
            $stock = new Stock();
            $stock->user_id = Auth::user()->id;
            $stock->guid = GuidHelper::getGuid();
            $stock->product_id = $product->id;
            $stock->quantity = $request->get('stockCapacity');
            $stock->save();

            //   $instock = new InStock();
            //   $instock->user_id = Auth::user()->id;
            //   $instock->guid = GuidHelper::getGuid();
            //   if($request->get('auctioned') == 'true'){
            //     $instock->listingdate = $request->get('auctionListing');
            //   }else if($request->get('sellingNow') == 'true'){
            //     $instock->listingdate = $request->get('listing');
            //   }
            //   $instock->productid = $product->id;
            //   $instock->quantity = $request->get('stockCapacity');
            //   $instock->save();

            /**
             * Stock Ends
             */

            if ($product) {
                return response()->json(['status' => 'true', 'product' => $product->id, 'data' => "Product has been Created!"], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Create Product!'], 403);
            }

            //     DB::commit();
            // } catch (\Exception $e) {
            //     DB::rollBack();
            //     throw $e;
            // }
            // return $this->genericResponse(true, 'Product Created', 200, ['product' => $product->withCategory()->withShop()->withAttributes()]);
        });
    }
    public function Imgupload(Request $request)
    {
        return DB::transaction(function () use (&$request) {
            $active = false;
            $product = new Product();

            $user = User::where('id', Auth::user()->id)->first();
            $store = SellerData::where('user_id', Auth::user()->id)->first();

            $product->user_id = Auth::user()->id;
            $product->name = 'title';
            $product->condition = 'condition';
            $product->model = 'model';
            $product->category_id = '1';
            $product->brand = 'brand';
            $product->stockcapacity = 0;
            $product->attributes = json_encode(['sizes']);
            $product->available_colors = json_encode(['availableColors']);
            $product->description = 'description';
            $product->selling_now = false;
            $product->price = 0;
            $product->sale_price = 0;
            $product->min_purchase = 1;
            $product->auctioned = 'auctions';
            $product->bids = 0;
            $product->durations = 0;
            $product->auction_listing = 'auctionListing';
            $product->deliverd_domestic = false;
            $product->tags = json_encode(['tags']);
            $product->deliverd_international = false;
            $product->delivery_company = 'deliverycompany';
            $product->is_sold = false;
            $product->street_address = "";//$request->get('street_address');//for later when google address will be implement
            $product->country = 'countryname';
            $product->city = 'cityname';
            $product->state = 'statesname';
            $product->IsSaved = true;
            $product->shipping_price = 0;
            $product->shipping_start = '2024-02-29 17:09:48';
            $product->shipping_end = '2024-02-29 17:09:48';
            $product->return_shipping_price = 0;
            $product->return_ship_duration_limt = 0;
            $product->return_ship_paid_by = 'admin';
            $product->return_ship_location = 'local';
            $product->shop_id = $store->id;
            $product->save();
            $file = $request->file('images');
            $extension = $file->getClientOriginalExtension();

            $guid = GuidHelper::getGuid();
            $path = User::getUploadPath() . StringHelper::trimLower(Media::PRODUCT_IMAGES);
            $name = "{$path}/{$guid}.{$extension}";
            $media = new Media();
            $media->fill([
                'name' => $name,
                'extension' => $extension,
                'type' => Media::PRODUCT_IMAGES,
                'user_id' => \Auth::user()->id,
                'product_id' => $product->id,
                'category_id' => $product->category_id,
                'active' => true,
            ]);

            $media->save();

            $image = Image::make($request->file('images'));
            $image->orientate();
            $image->resize(300, 300);
            $image->stream();
            Storage::put('public/' . $name, $image->encode());

            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
                'guid' => $media->guid,
                'product' => $product->guid,
            ];
        });
    }

    public function getAttributes(Request $request, $categoryID)
    {
        return CategoryAttributes::where('category_id', $categoryID)
            ->with([
                "attribute" => function ($query) {
                    $query->select(Attribute::defaultSelect());
                },
                "category" => function ($query) {
                    $query->select(Category::defaultSelect());
                }
            ])->get();
    }
    public function CategoryAttributes($id)
    {
        try {
            $categoryAttributes = CategoryAttributes::where('category_id', $id)->get();
            if ($categoryAttributes) {
                $attributes = [];
                foreach ($categoryAttributes as $value) {
                    $attribute = Attribute::find($value->attribute_id);

                    $data = [
                        "key"=>$attribute->name,
                       "options" => json_decode(json_encode($attribute->options, true), true)
                    ];

                    $attributes[] = $data;

                }
                return response()->json(['status' => true, 'message' => "Attributes Found!", 'data' => $attributes], 200);
            } else {
                return response()->json(['status' => false, 'message' => "No Attributes Found!", 'data' => []], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []], 400);
        }

    }
    public function active(Request $request)
    {
        $user_id=Auth::user()->id??$request->user_id;
        $productNormal = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                //->where('products.price', '<>', null)
                ->with(['user'])
                ->with(['category'])
                ->with(['brand'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('user_id', '=', $user_id)
              ->where('products.stockcapacity', '>', 0)
                ->where('auctioned', 0)
                ->where('products.is_deleted', 0)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            foreach ($productNormal as $product) {
                $test = json_decode(json_decode($product->attributes, true), true);
                $attributes = [];
                if (isset($test)) {
                    foreach ($test as $te) {
                        if (!isset($attributes[$te['key']])) {
                            //$attributes[$te['key']] = [];
                        }
                        $data = [
                            "key"=>$te['key'],
                           "options" => $te['value']
                        ];
                        $attributes[] = $data;
                    }
                } else {
                    $attributes = [];
                }
                $product->attributes = $attributes;
                $product->auction_remainig_time=0;
            }
             $productAuctioned = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
                ->where('products.active', true)
                // ->where('products.weight', '<>', null)
                ->with(['user'])
                ->with(['category'])
                ->with(['brand'])
                ->with(['media'])
                ->with(['savedUsers'])
                ->with(['shop'])
                ->where($this->applyFilters($request))
                ->where('products.is_sold', false)
                ->where('products.is_deleted', 0)
                ->where('user_id', '=', $user_id)
                    ->where('auction_End_listing', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('auctioned', 1)
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
            $product = array_merge(json_decode($productNormal), json_decode($productAuctioned));
            $data=[];
            foreach($product as $pro)
            {
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                     "bid_price"=>$pro->bids,
                          "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage
                    
                ];
                $data[]=$arr;
            }
            if($product){
                return response()->json(['status' => true,"data"=>$data,"message"=>"Active Products"], 200);
            }
            else {
            return response()->json(['status' => false, 'data' =>[], "message"=>"Unable to Get active Products"], 200);
        }
    } 
    public function inactive(Request $request)
    {
        $products = Product::
            // where('active', false)
            where('is_deleted', 0)
            // ->orWhere('is_sold',true)
            ->where('user_id', Auth::user()->id)
            ->with('user')
            ->get();
        $productList = array();
        foreach ($products as $product) {
            if ($product->recurring) {
                $product->soldstatus = 'Sold Out';
                array_push($productList, $product);
            } else {
                $product->soldstatus = 'Out of Stock';
                array_push($productList, $product);
            }
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        //$attributes[$te['key']] = [];
                    }
                    $data = [
                        "key"=>$te['key'],
                       "options" => $te['value']
                    ];
                    $attributes[] = $data;
                }
            } else {
                $attributes = [];
            }
            $product->attributes = $attributes;
            $product->auction_remainig_time=0;
            if($product->auctioned==1){
                      $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
            }
        }
        //ArrayHelper::merge($request->all(), ['guid' => GuidHelper::getGuid()]))
        if ($productList) {
             $data=[];
            foreach($productList as $pro)
            {
                  if ($pro->recurring) {
                $pro->soldstatus = 'Sold Out';
             
            } else {
                $pro->soldstatus = 'Out of Stock';
            }
                $arr=[
                    "id"=>$pro->id,
                    "guid"=>$pro->guid,
                    "name"=>$pro->name,
                    "auctioned"=>$pro->auctioned,
                    "price"=>$pro->price,
                    "sale_price"=>$pro->sale_price,
                    "media"=>$pro->media,
                    "recurring"=>$pro->recurring,
                    "soldstatus"=>$pro->soldstatus,
                    "bid_price"=>$pro->bids,
                         "is_favourite"=>$pro->is_favourite,
                     "favourite_count"=>$pro->favourite_count,
                     "underage"=> $pro->underage
                ];
                $data[]=$arr;
            }
            return response()->json(['status' => true, 'data' => $data], 200);
        } else {
            return response()->json(['status' => false, 'data' =>[], "message"=>"Unable to Get Inactive Products"], 200);
        }
    }
    public function getProductAttributes(Request $request, $productID)
    {
        $product = Product::where('guid', $productID)->first();
        return ProductsAttribute::where('product_id', $product->id)
            ->with([
                "attribute" => function ($query) {
                    $query->select(Attribute::defaultSelect());
                },
                "product" => function ($query) {
                    $query->select(Product::defaultSelect());
                }
            ])->get();
    }



    /**
     * @param Product $product
     * @return Product
     */
    public function show(Product $product)
    {
        $product->price = $product->getPrice();

        return $product->withCategory()
            // ->withAttributes()
            ->withShop()
            // ->withBids()
            // ->appendDetailAttribute()
            ->withUser();
    }
    public function getTrendingProduct($id)
    {
        $product = Product::where('guid', $id)->first();
        $storeId = SellerData::where('user_id', $product->user_id)->first();
        return Product::where('shop_id', $storeId->id)
            ->where('is_sold', 1)
            ->count();
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

    public function getSavedAddress($guid)
    {
        $product = Product::where('guid', $guid)->first();
        $saveAddress = SaveAddress::where('user_id', Auth::user()->id)
            ->where('product_id', $product->id)
            ->first();
        if ($saveAddress) {
            return $saveAddress;

        } else {
            return SaveAddress::where('user_id', Auth::user()->id)
                ->get()
                ->last();
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();
         if($input['auctioned']==0)
        {
            if (isset($input['saleprice']) && !empty($input['saleprice'])  && $input['saleprice']!="undefined" && $input['saleprice']!="null" && $input['saleprice'] !=null && $input['saleprice'] >= $input['price']) {
                return response()->json(['status' => false, 'message' => "Discount price must be less than the actual price", 'data' => []], 400);
            }
        }
        return DB::transaction(function () use ($request, $product) {
            $getBrand=Brands::where('name','=',$request->brands)->first();
            $brandID=null;
            if($getBrand){
                $brandID=$getBrand->id;
            }
            else{
                if(!empty($request->brands))
                {
                    $brand= Brands::create([
                        "name"=>$request->brands,
                        "guid"=>Str::uuid()
                    ]);
                    $brandID=$brand->id;
                }
            }
            // $country = Countries::where('id', $request->get('country'))->first();
            // $states = State::where('id', $request->get('states'))->first();
            // $city = City::where('id', $request->get('city'))->first();
            $user = User::where('id', Auth::user()->id)->first();
            $store = SellerData::where('user_id', Auth::user()->id)->first();
            $sellingNow = 0;
            if ($request->get('sellingNow') == 'true') {
                $sellingNow = 1;
            }
            $auctioned = 0;
            if ($request->get('auctioned') == 'true') {
                $auctioned = 1;
            }
            $deliverdDomestic = 0;
            if ($request->get('deliverddomestic') == 'true') {
                $deliverdDomestic = 1;
            }
            $deliverdInternational = 0;
            if ($request->get('deliverdinternational') == 'true') {
                $deliverdInternational = 1;
            }
            $recurring = false;
            $cap=$request->get('stockCapacity')??0;
            if ($request->get('stockCapacity') > 1) {
                $recurring = true;
            } else if ($request->get('stockCapacity') == 1) {
                $recurring = false;
            }
            else{
                $recurring = false;
                $cap=0;
            }

            $products = Product::where('id', $product->id)->update([
                "user_id" => Auth::user()->id,
                "name" => $request->get('title'),
                "condition" => $request->get('condition'),
                "model" => $request->get('model'),
                "category_id" => $request->get('category'),
                "brand_id" => $brandID,
                "stockcapacity" => $cap,
                "attributes" => json_encode($request->get('attributes')),
                // "available_colors" => json_encode($request->get('availableColors')),
                "description" => $request->get('description'),
                "selling_now" => $sellingNow,
                "price" => $request->get('price'),
                "postal_address" => $request->get('address'),
                "street_address" => $request->get('address'),
                "sale_price" => $request->get('saleprice'),
                "min_purchase" => $request->get('minpurchase'),
                "auctioned" => $auctioned,
                "bids" => $request->get('bids'),
                "durations" => $request->get('durations'),
                "auction_listing" => $request->get('auctionListing'),
                "auction_End_listing" => $request->get('end_listing'),
                "deliverd_domestic" => $deliverdDomestic,
                "tags" => json_encode($request->get('tags')),
                "deliverd_international" => $deliverdInternational,
                "delivery_company" => $request->get('deliverycompany'),
                "country" => $request->get('country'),
                "city" => $request->get('city'),
                "state" => $request->get('states'),
                "shipping_price" => $request->get('shippingprice'),
                // "shipping_start" => $request->get('shippingstart'),
                // "shipping_end" => $request->get('shippingend'),
                "return_shipping_price" => $request->get('returnshippingprice'),
                "return_ship_duration_limt" => $request->get('returndurationlimit'),
                "return_ship_paid_by" => $request->get('returnshippingpaidby'),
                "shop_id" => $store->id,
                "recurring" => $recurring,
                "weight"=>$request->get('weight')??1,
            "underage"=>$request->get('underage')??1,
            "used_condition"=>$request->get('used_condition')??"",
            "height"=>$request->get('height')??1,
            "width"=>$request->get('width')??1,
            "length"=>$request->get('length')??1,
            "weight"=>$request->get('weight')??1,
            ]);

            if(isset($request->old_files))
            {
                Media::where('product_id',$product->id)->whereNotIn('id',json_decode($request->old_files))->delete();
            }
            if(isset($request->old_files_web))
            {
                Media::where('product_id',$product->id)->whereIn('id',json_decode($request->old_files_web))->delete();
            }
            // else{
            //     Media::where('product_id',$product->id)->delete();
            // }
            $imageName = [];
            if ($request->hasFile('file')) {

                foreach ($request->file('file') as $file) {

                    $extension = $file->getClientOriginalExtension();
                    $guid = GuidHelper::getGuid();
                    // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
                    $name = "{$guid}.{$extension}";
                    $path = 'images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $product->id . '/' . "{$guid}.{$extension}";
                    $pathName = env('APP_URL').'/images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $product->id . '/' . "{$guid}.{$extension}/" . "{$guid}.{$extension}";
                    $media = new Media();
                    // $name = 'images/'.Product::MEDIA_UPLOAD.'/'.$user->id.'/'. $product->id.'/'."{$guid}.{$extension}";
                    $properties = [
                        'name' => $pathName,
                        'extension' => $extension,
                        'type' => Product::MEDIA_UPLOAD,
                        'user_id' => Auth::user()->id,
                        'product_id' => $product->id,
                        'url' => $pathName,
                        'active' => true
                    ];

                    $media->fill($properties);
                    $media->save();
                    // $path = User::getUploadPath(Auth::user()->id) . StringHelper::trimLower(Media::PRODUCT_IMAGES);
                    $image = Image::make($file);
                    $image->orientate();
                  $image->resize(300, 300);
                    $image->stream();
                    $file->move($path, "{$guid}.{$extension}");
                }
            }
            


            /**
             * For Product Attributes Start
             */
            // $attributes = ProductAttributes::where('product_id', $product->id)->first();
            // if($attributes){
            //     ProductAttributes::where('product_id', $product->id)->delete();
            // }
            // $sizes = json_decode($request->get('sizes'));
            // foreach($sizes as $size){
            //     foreach($size as $key => $siz){
            //         $productattributes =new ProductAttributes();
            //         $productattributes->name=$key;
            //         $productattributes->value=$siz;
            //         $productattributes->product_id=$product->id;
            //         $productattributes->save();
            //     }
            // }
            /**
             * For Product Attributes Ends
             */

            if ($products) {
                return response()->json(['status' => 'true', 'product' => $product->id, 'data' => "Product has been Updated!"], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Update Product!'], 403);
            }
            // return $this->genericResponse(true, "$product->name Updated", 200, ['product' => $product->withCategory()]);
        });
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // return DB::transaction(function () use (&$request, &$id) {
        //     $product = Product::where('guid', $id)->first();
        //     Product::where('guid', $id)->delete();
        //     Stock::where('product_id', $product->id)->delete();
        //     return response()->json(['message' => 'Product Deleted Successfully'], 200);
        // });
        return DB::transaction(function () use (&$request, &$id) {
            $product = Product::where('guid', $id)->first();
            Product::where('guid', $id)->delete();
            Stock::where('product_id', $product->id)->delete();
            RecentView::where('product_id', $product->id)->delete();
            RecentUserView::where('product_id', $product->id)->delete();
            return response()->json(['message' => 'Product Deleted Successfully'], 200);
        });
    }

    public function ratings($id, Request $request)
    {
        Product::where('guid', $id)->update(['ratings_count' => $request->get('ratings')]);
        $data = [
            'product_id' => $request->get('product_id'),
            'user_id' => $request->get('user_id'),
            'order_id' => $request->get('order_id')
        ];
        $productRatings = new ProductRatings($data);
        $productRatings->save();

        return response()->json(['message' => 'Thankyou for Rating'], 200);
    }

    public function checkRatings($productId, $userId, $orderId, Request $request)
    {
        return ProductRatings::where('product_id', $productId)
            ->where('user_id', $userId)
            ->where('order_id', $orderId)->first();
        // return response()->json(['message' => 'Product Updated Successfully'], 200);
    }

    public function media(Product $product, Request $request)
    {
        return $product->images();
    }

    public function upload_(Product $product, Request $request)
    {
        if ($request->hasFile('file')) {
            $image = Image::make($request->file('file'));
            /**
             * Main Image Upload on Folder Code
             */
            $imageName = time() . '-' . $request->file('file')->getClientOriginalName();
            $destinationPath = public_path('image/');
            // $image->resize(1024,1024);
            $image->resize(300, 300);
            $image->save($destinationPath . $imageName);
        }
    }
    public function imageResize($image)
    {
        $image = Image::make($image);

        return $image->resize(1024, 1024, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    /**
     * @param Product $product
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function upload(Product $product, Request $request)
    // public function upload(Request $request)
    {
        return DB::transaction(function () use (&$request) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            $path = User::getUploadPath() . StringHelper::trimLower(Media::PRODUCT_IMAGES);
            $name = "{$path}/{$guid}.{$extension}";
            $media = new Media();
            $media->fill([
                'name' => $name,
                'extension' => $extension,
                'type' => Media::PRODUCT_IMAGES,
                'user_id' => \Auth::user()->id,
                'product_id' => $product->id,
                'active' => true,
            ]);

            $media->save();

            $image = Image::make($request->file('file'));
            $image->orientate();
            $image->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            //email:flexehome123@gmail.com
            //[pass:123flexeaccount_
            //users/5/product
            $image->stream();
            // dd($image);
            // Storage::putFileAs('public/watermarked/', $watermarkedImage, $fileName . $extension);
            // Storage::put('public/watermarked/' . $fileName . $extension, $watermarkedImage->encode());
            Storage::put('public/' . $name, $image->encode());
            // Storage::put('public/'. $path, $image, 'public');
            // Storage::disk('local')->put('public/'. $path .'/'.$name, $image, 'public');
            // Storage::putFileAs(
            //     'public/' . $path,
            //     $image,
            //     "{$guid}.{$extension}"
            // );
            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
                'guid' => $media->guid,
                'productguid' => $product->guid
            ];
        });
    }
    public function imageUploadProduct(Request $request)
    {
        //$file =$request->file('image');
        //  if($request->hasFile('image')){
        try {
           
       
        $imageName = [];
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $extension = $file->getClientOriginalExtension();
                $guid = GuidHelper::getGuid();
                // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
                $name = "{$guid}.{$extension}";
                $path = 'images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $request->get("product_id") . '/' . "{$guid}.{$extension}";
                $pathName = env('APP_URL').'/images/' . Product::MEDIA_UPLOAD . '/' . Auth::user()->id . '/' . $request->get("product_id") . '/' . "{$guid}.{$extension}/" . "{$guid}.{$extension}";
                $media = new Media();
                // $name = 'images/'.Product::MEDIA_UPLOAD.'/'.$user->id.'/'. $product->id.'/'."{$guid}.{$extension}";
                $properties = [
                    'name' => $pathName,
                    'extension' => $extension,
                    'type' => Product::MEDIA_UPLOAD,
                    'user_id' => Auth::user()->id,
                    'product_id' => $request->get("product_id"),
                    'url' => $pathName,
                    'active' => true
                ];

                $media->fill($properties);
                $media->save();
                // $path = User::getUploadPath(Auth::user()->id) . StringHelper::trimLower(Media::PRODUCT_IMAGES);
                $image = Image::make($file);
                $image->orientate();
                $image->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->stream();
                $file->move($path, "{$guid}.{$extension}");
                return response()->json(['status'=>true,'message' => 'success'], 200);
            }
        }
        //  }
    } catch (\Throwable $th) {
        return response()->json(['message' => $th->getMessage()], 400);
    }

    }

    public function searched(Request $request)
    {
        return $request->get('query');
        die();
        // if ($request->get('lat') && $request->get('lng')) {

        //     $latitude = abs($request->get('lat'));
        //     $longitude = abs($request->get('lng'));

        //     $products = Product::where('active', true)->where('is_sold', false)
        //     ->where('IsSaved', true)
        //     ->with(['savedUsers'])
        //     ->with(['user'])
        //     ->where('name', 'LIKE', "%{$request->get('query')}%")
        //     ->when($request->has('min_price'), function ($query) use ($request) {
        //             $min_price = $request->get('min_price');
        //             $max_price = $request->get('max_price');
        //             if ($max_price) {
        //                 $query->whereBetween('price', [$min_price, $max_price]);
        //             } else {
        //                 $query->where('price', ">", $min_price);
        //             }
        //         })->with('order', function ($query) {
        //             $query->where('buyer_id', '=', Auth::guard('api')->id());
        //         })
        //         ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
        //             // $builder->where('parent_category_id', $category);
        //             $builder->where('category_id', $category);
        //             $builder->where('is_sold', false);
        //             $builder->where('IsSaved', true);
        //             $builder->where('active', true)
        //             // $builder->where('category_id', $category)
        //                 ->when(json_decode($request->get('filters'), true), function (Builder $builder, $filters) {
        //                     $having = [];

        //                     foreach ($filters as $id => $value) {
        //                         if (is_bool($value)) {
        //                             $value = $value ? 'true' : 'false';
        //                         }

        //                         if (is_array($value)) {
        //                             $value = implode('","', $value);
        //                             $having[] = "sum(case when products_attributes.attribute_id = $id and json_overlaps(products_attributes.value, '[\"$value\"]') then 1 else 0 end) > 0";
        //                         } else {
        //                             $having[] = "sum(case when products_attributes.attribute_id = $id and json_contains(products_attributes.value, '\"$value\"') then 1 else 0 end) > 0";
        //                         }
        //                     }

        //                     $having = implode(' and ', $having);
        //                     $builder->whereRaw("
        //                         id in
        //                         (select products.id
        //                         from products
        //                         inner join products_attributes on products.id = products_attributes.product_id
        //                         group by products.id
        //                         having $having)
        //                     ");
        //                 });
        //         })
        //         ->orderBy(DB::raw("3959 * acos( cos( radians({$latitude}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$longitude}) ) + sin( radians({$latitude}) ) * sin(radians(latitude)) )"), 'DESC')
        //         ->orderByDesc('featured')
        //         ->orderByDesc('created_at')
        //         ->get();
        // } else {
        // $products = Product::where('active', true)->where('is_sold', false)
        //     ->where('name', 'LIKE', "%{$request->get('query')}%")
        //     ->where('parent_category_id', $request->get('category_id'))
        //     ->where('category_id', $request->get('category_id'))
        //     ->when($request->has('min_price'), function ($query) use ($request) {
        //         $min_price = $request->get('min_price');
        //         $max_price = $request->get('max_price');
        //         if ($max_price) {
        //             $query->whereBetween('price', [$min_price, $max_price]);
        //         } else {
        //             $query->where('price', ">", $min_price);
        //         }
        //     })
        //     ->with('order', function ($query) {
        //         $query->where('buyer_id', '=', Auth::guard('api')->id());
        //     })  
        //     // ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
        //     //     // $builder->orWhere('category_id', $category);
        //     //     return $category;
        //     // });
        //     ->distinct()
        //     ->orderByDesc('featured')
        //     ->orderByDesc('created_at')
        //     ->get();
        //////////////////$products = Product::where('active', true)
        ////////////////// ->where('IsSaved', true)
        ////////////////// ->with(['savedUsers'])
        ////////////////// ->with(['user'])
        ///////////////->where('name', 'LIKE', "%{$request->get('query')}%")
        // ->when($request->has('min_price'), function ($query) use ($request) {
        //     $min_price = $request->get('min_price');
        //     $max_price = $request->get('max_price');
        //     if ($max_price) {
        //         $query->whereBetween('price', [$min_price, $max_price]);
        //     } else {
        //         $query->where('price', ">", $min_price);
        //     }
        // })
        // ->with('order', function ($query) {
        //     $query->where('buyer_id', '=', Auth::guard('api')->id());
        // })  
        // ->when($request->get('category_id'), function (Builder $builder, $category) use ($request) {
        //     // $builder->where('parent_category_id', $category);
        //     $builder->where('is_sold', false);
        //     $builder->where('IsSaved', true);
        //     // $builder->where('is_sold', false);
        //     $builder->where('active', true);
        //     $builder->where('category_id', $category)
        //     // $builder->where('category_id', $category)
        //     // $builder->where('category_id', $category)
        //     // $builder->where('category_id', $category)
        //         ->when(json_decode($request->get('filters'), true), function (Builder $builder, $filters) {
        //             $having = [];

        //             foreach ($filters as $id => $value) {
        //                 if (is_bool($value)) {
        //                     $value = $value ? 'true' : 'false';
        //                 }

        //                 if (is_array($value)) {
        //                     $value = implode('","', $value);
        //                     $having[] = "sum(case when products_attributes.attribute_id = $id and json_overlaps(products_attributes.value, '[\"$value\"]') then 1 else 0 end) > 0";
        //                 } else {
        //                     $having[] = "sum(case when products_attributes.attribute_id = $id and json_contains(products_attributes.value, '\"$value\"') then 1 else 0 end) > 0";
        //                 }
        //             }

        //             $having = implode(' and ', $having);
        //             // $builder->whereRaw("
        //             //     id in
        //             //     (select products.id
        //             //     from products
        //             //     inner join products_attributes on products.id = products_attributes.product_id
        //             //     right join categories on products.category_id = categories.id
        //             //     group by products.id
        //             //     having $having)
        //             // ");
        //             $builder->whereRaw("
        //                 id in
        //                 (select products.id
        //                 from products
        //                 inner join products_attributes on products.id = products_attributes.product_id
        //                 right join categories on products.category_id = categories.id
        //                 group by products.id
        //                 having $having)
        //             ");
        //         });
        // })
        //////////////->distinct()
        // ->orderByDesc('featured')
        //////////////->orderByDesc('created_at')
        //////////////->get();
        // }

        // $category = Category::when($request->get('category_id'), function (Builder $builder, $category) {
        //     $builder->where('id', $category)
        //         ->with('attributes');
        // })
        //     ->where('type', Category::PRODUCT)
        //     ->get();

        // $categories = Category::with('attributes')->where('type', Category::PRODUCT)->get();

        // $searched= Product::get();
        // if($searched){
        //     return response()->json(['status'=> true,'data' =>$searched], 200);       
        // }else{
        //     return response()->json(['status'=> false,'data' => 'Unable to Fetch Product'], 400);        
        // }
        // return [
        //     'results' => $products,
        //     'categories' => $categories,
        //     'category' => $category
        // ];
    }
    public function getProductByPrice(Request $request, $price)
    {
        $product = Product::where('price', $price)->get();
        if ($product) {
            return response()->json(['status' => true, 'data' => $product], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Products"], 400);
        }
    }
    public function getAuctionedProducts(Request $request)
    {
        $products = Product::where('auctioned', true)->get();
        if ($products) {
            return response()->json(['status' => true, 'data' => $products], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Products"], 400);
        }

    }
    public function getProductBySize(Request $request, $size)
    {
        $products = Product::get();
        $attributes = [];
        $finalAttributes = [];
        foreach ($products as $product) {
            $attributes = [$product->id => json_decode($product->attributes)];
            array_push($finalAttributes, $attributes);
        }
        foreach ($finalAttributes as $finalAttribute) {
            print_r($finalAttribute);
        }
        // return $finalAttributes;
        // if($product){
        //     return response()->json(['status'=> true,'data' =>$product], 200);       
        // }else{
        //     return response()->json(['status'=> false,'data' =>"Unable To Get Products"], 400);       
        // }
    }
    public function getProductByPriceRange(Request $request, $min, $max)
    {
        $product = Product::whereBetween('price', [$min, $max])->get();
        if ($product) {
            return response()->json(['status' => true, 'data' => $product], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Products"], 400);
        }
    }
    public function getMin(Request $request)
    {
        $minimum = [];
        $products = Product::where('active', true)
            ->get();
        foreach ($products as $product) {
            array_push($minimum, $product->price);
        }
        if ($minimum) {
            return response()->json(['status' => true, 'data' => min($minimum)], 200);
        } else {
            return response()->json(['status' => false, 'data' => 'Unable to Fetch Price'], 400);
        }
    }
    public function getMax(Request $request)
    {
        $maximum = [];
        $products = Product::where('active', true)
            ->get();
        foreach ($products as $product) {
            array_push($maximum, $product->price);
        }
        if ($maximum) {
            return response()->json(['status' => true, 'data' => max($maximum)], 200);
        } else {
            return response()->json(['status' => false, 'data' => 'Unable to Fetch Price'], 400);
        }
    }
    public function getByCategory(Request $request, $id)
    {
        $products = Product::where('active', true)
            ->where('category_id', $id)
            ->get();
        if ($products) {
            return response()->json(['status' => true, 'data' => $products], 200);
        } else {
            return response()->json(['status' => false, 'data' => 'Unable to Fetch Products'], 400);
        }
    }
    // public function categories(Request $request){
    //     $products = Product::where('active',true)
    //     ->get();
    //     $category = [];
    //     foreach($products as $pro){
    //         $cat = Category::where('id',$pro->category_id)->first();
    //         $category=[$cat->id];
    //         // array_push($category, $cat->id);
    //         return $category;
    //         // array_push($category, $cat);
    //     }
    //     $resultCategory = array_unique($category);
    //     return $resultCategory;
    //     // if($resultCategory){
    //     //     return response()->json(['status'=> true,'data' => $resultCategory], 200);       
    //     // }else{
    //     //     return response()->json(['status'=> false,'data' => 'Unable to Fetch Category'], 400);        
    //     // }
    // }
    public function categories(Request $request)
    {
        $products = Product::where('active', true)
            ->get();
        $category = [];
        foreach ($products as $pro) {
            $cat = Category::where('id', $pro->category_id)->first();
            array_push($category, $cat->id);
            // array_push($category, $cat);
        }

        $resultCategory = array_unique($category);
        $resultCategories = array_values($resultCategory);
        $categories = Category::whereIn('id', $resultCategories)->get();
        if ($categories) {
            return response()->json(['status' => true, 'data' => $categories], 200);
        } else {
            return response()->json(['status' => false, 'data' => 'Unable to Fetch Category'], 400);
        }
    }

    public function getSizes(Request $request)
    {
        $attributes = ProductAttributes::get();
        $sizeattr = [];
        foreach ($attributes as $attribute) {
            if ($attribute->name == "size") {
                array_push($sizeattr, $attribute->value);
            }
        }
        $arr = array_unique($sizeattr);
        $sizeattrUnique = array_values($arr);

        if ($sizeattrUnique) {
            return response()->json(['status' => true, 'data' => $sizeattrUnique], 200);
        } else {
            return response()->json(['status' => false, 'data' => 'Unable to Fetch Sizes'], 400);
        }
        // $sizes = [];
        // $products = Product::where('active',true)
        // ->get();
        // foreach($products as $product){
        //     array_push($sizes, json_decode($product->attributes));
        // } 
        // $selectedSizes = [];
        // foreach($sizes as $size){
        //     foreach($size as $siz){
        //         array_push($selectedSizes,$siz->size);
        //     }
        // }
        // $givensizes = array_unique($selectedSizes);
        // $givnsiz = [];
        // foreach($givensizes as $givensiz){
        //     array_push($givnsiz, $givensiz);
        // }
        // if($givnsiz){
        //     return response()->json(['status'=> true,'data' => $givnsiz], 200);       
        // }else{
        //     return response()->json(['status'=> false,'data' => 'Unable to Fetch Sizes'], 400);        
        // }
    }

    /**
     * Saved user products
     * @param Product $product
     * @param Request $request
     */
    public function Saved(Product $product, Request $request)
    {
        return $product->attachOrDetachSaved();
    }

    /*
     * @param Product $product
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function offer(Product $product, Request $request)
    {
        $offer = $request->get('offer');
        $chat_id;

        // optimize move this into the request
        if ($offer >= $product->price) {
            throw new \Exception('Your offer should be less than the prduct price.');
        }

        if ($offer <= 0) {
            throw new \Exception('Your offer is invalid.');
        }

        $sender = Auth::user();
        $recipient = $product->user;

        if ($sender->id === $recipient->id) {
            throw new \Exception('Unable to make an offer on your own product');
        }

        if ($sender->id > $recipient->id) {
            $chat_id = $recipient->id . $sender->id;
        } else {
            $chat_id = $sender->id . $recipient->id;
        }

        $message = new Message();
        $message->sender_id = $sender->id;
        $message->product_id = $product->guid;
        $message->chat_id = $chat_id;
        $message->recipient_id = $recipient->id;
        $message->data = $sender->name . ' has made an offer of ' . $offer . ' for ' . $product->name;
        $message->notifiable_id = $product->id;
        $message->notifiable_type = Product::class;
        $message->save();

        Offer::request($product, $offer);

        OfferMade::trigger($recipient);

        $notifiable_user = new OfferUser();
        $notifiable_user->name = $recipient->name;
        $notifiable_user->email = $recipient->email;
        $notifiable_user->sender = $sender->name;
        $notifiable_user->price = $offer;
        $notifiable_user->product = $product->name;

        $userofferproduct = new UserOfferProduct();
        $userofferproduct->userId = $sender->id;
        $userofferproduct->productId = $product->id;
        $userofferproduct->status = '1';
        $userofferproduct->save();

        $recipient->notify(new OfferMadeNotification($notifiable_user));

        return $this->genericResponse(true, 'Offer made successfully.');
    }

    public function getSaved()
    {
        if (Auth::check()) {

            $user = User::where('id', Auth::user()->id)->with('savedProducts')->with('shop')
                // ->with('savedServices')
                ->first();

            if ($user) {
                return response()->json(['status' => true, 'data' => $user], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Unable to get WishList'], 500);
            }
            // $data = array_merge(json_decode($user->savedProducts));//, json_decode($user->savedServices));
            // return $user->savedServices;
            // return response()->json([
            //     'user' => Auth::user()->id,
            //     'data' => $user->savedProducts->shop,
            // ], 200);
        }
    }
    public function getSaveByUser()
    {
        if (Auth::check()) {
            // return SavedUsersProduct::where('user_id', Auth::user()->id)->get();
            return SavedUsersProduct::get();
        }
    }
    public function deleteMedia(Media $media)
    {
        if (Auth::user()->id == $media->user_id) {
            Storage::delete($media->name);
            $media->delete();
        }
    }

    public function getBuyingOffers()
    {

        $user = Auth::user();
        // return Offer::where("requester_id","=",Auth::guard('api')->id())
        // // ->leftJoin('orders','offers.id','=','orders.offer_id')
        // ->where('status_name', Offer::$STATUS_NEW_REQUEST)
        // ->with(["product"=>function (BelongsTo $hasMany){
        //     $hasMany->select(Product::defaultSelect());
        // }, "user" => function (BelongsTo $hasMany) {
        //     $hasMany->select(Product::getUser());
        // }])
        // ->get();
        return Offer::where("requester_id", "=", Auth::guard('api')->id())
            // ->where('status_name', Offer::$STATUS_NEW_REQUEST)
            ->whereHas('product', function ($query) {
                $query->where('is_sold', '=', false);
            })->with([
                    "product" => function (BelongsTo $hasMany) {
                        $hasMany->select(Product::defaultSelect());
                    },
                    "user" => function (BelongsTo $hasMany) {
                        $hasMany->select(Product::getUser());
                    }
                ])->get();

    }

    public function getOrderdProduct(Request $request)
    {
        return DB::table('orders')
            ->select('*')
            ->join('offers', 'orders.offer_id', '=', 'offers.id')
            ->where('offers.status_name', '=', 'Accepted')
            ->get();
    }

    public function getSellingOffers()
    {
        $user = Auth::user();
        return $user->sellingOffers()
            ->where('status_name', Offer::$STATUS_NEW_REQUEST)
            ->whereHas('product', function ($query) {
                $query->where('is_sold', '=', false);
            })->with([
                    "product" => function (BelongsTo $hasMany) {
                        $hasMany->select(Product::defaultSelect());
                    },
                    "requester" => function (BelongsTo $hasMany) {
                        $hasMany->select(User::defaultSelect());
                    }
                ])->get();

        // return Offer::where("user_id","=",Auth::guard('api')->id())
        // // ->with(["product"=>function(BelongsTo $hasMany){
        // //     $hasMany->select(Product::defaultSelect());
        // // }, "user" => function (BelongsTo $hasMany) {
        // //     $hasMany->select(Product::getUser());
        // // }])
        // ->get();

    }


    public function feature(Product $product, Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

        $days = $request->get('days');
        if (
            $paymentIntent->id === $request->get('payment_intent') &&
            $paymentIntent->status === 'succeeded' &&
            $paymentIntent->amount === (Product::getFeaturedPrice($days) * 100)
        ) {
            $product->featured = true;
            $product->featured_until = Carbon::today()->addDays($days);
            $product->update();
        }

        return $product;
    }
    public function userRating($id)
    {
        $userRating = Product::where('user_id', $id)->get();
        // $ratingCount = $userRating->avg('ratings_count');
        $ratingsCount = [];
        foreach ($userRating as $key => $rating) {
            if ($rating->ratings_count) {
                array_push($ratingsCount, $rating->ratings_count);
            }
        }
        if ($ratingsCount) {
            $rateCount = count($ratingsCount);
            $rateSum = array_sum($ratingsCount);
            $rateTotal = $rateSum / $rateCount;
            return $rateTotal;
        } else {
            return 0;
        }
    }
    public function hire(Product $product, Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));

        $days = $request->get('days');
        if (
            $paymentIntent->id === $request->get('payment_intent') &&
            $paymentIntent->status === 'succeeded' &&
            $paymentIntent->amount === (Product::getHirePrice($days) * 100)
        ) {
            $product->hired = true;
            $product->hired_until = Carbon::today()->addDays($days);
            $product->update();
        }

        return $product;
    }
    public function checkUserProductOffer($id, $guid)
    {

        $product = Product::where('guid', $guid)->first();
        $user = User::where('id', $id)->first();
        $offer = Offer::where('requester_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
        return $offer;

    }

    public function getProductbyStore(Request $request, $storeId)
    {

        $products = Product::join('categories as categories', 'categories.id', '=', 'products.category_id')
            ->where('products.active', true)
            //->where('products.price', '<>', null)
            ->with(['user'])
            ->with(['savedUsers'])
            ->where($this->applyFilters($request))
            ->orderByDesc('products.created_at')
            ->get([
                'categories.name as category',
                'products.*'
            ]);
        return $products;
    }

    public function getcategorybyStore(Request $request, $storeId)
    {
        $products = Product::with('category')
            ->where('shop_id', $storeId)
            ->get();
        return $products;
    }
    public function results(Request $request, $search)
    {
        $products = Product::with('category')
            ->where('name', 'like', '%' . $search . '%')
            ->get();
        if ($products) {
            return response()->json(['status' => true, 'data' => $products], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Related Products"], 400);
        }
    }

    public function relatedProduct(Request $request, $guid)
    {
        $category = Category::where('guid', $guid)->first();
        $products = Product::with('category')
            ->with(['user'])
            ->with(['media'])
            ->with(['savedUsers'])
            ->where('category_id', $category->id)
            ->get();
        $data = [
            'products' => $products,
            'category' => $category
        ];
        if ($products) {
            return response()->json(['status' => true, 'data' => $data], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Related Products"], 400);
        }
    }
    public function savedSearch(Request $request)
    {
        return DB::transaction(function () use ($request) {
            //Delete previous Searches if exists
            $getsavesearch = SaveSearch::where('user_id', Auth::user()->id)
                ->where('keywords', $request->get("keywords"))->delete();
            //Insert new save searches
            $savedSearches = new SaveSearch();
            $savedSearches->user_id = Auth::user()->id;
            $savedSearches->keywords = $request->get("keywords");
            $savedSearches->email_alert = $request->get("emailAlert");
            $savedSearches->guid = GuidHelper::getGuid();
            $savedSearches->save();
            if ($savedSearches) {
                return response()->json(['status' => true, 'data' => 'Saved on saved searches'], 200);
            } else {
                return response()->json(['status' => false, 'data' => "Unable To Saved on saved searches"], 400);
            }
        });
    }
    public function getSavedSearch(Request $request)
    {
        $savedSearches = SaveSearch::where('user_id', Auth::user()->id)->get();
        if ($savedSearches) {
            return response()->json(['status' => true, 'data' => $savedSearches], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get  Saved searches"], 400);
        }
    }
    public function getRecomemdedProducts(Request $request, $shops)
    {
        return $shops;
        // $products = Product::whereIn('shop_id', $request->get('shops'))->get();
        // if($products){
        //     return response()->json(['status'=> true,'data' =>$products], 200);       
        // }else{
        //     return response()->json(['status'=> false,'data' =>"Unable To Get Recomended Products"], 400);       
        // }
    }
    public function getCompanies(Request $request)
    {
        $deleiverycomapny = DeliverCompany::get();
        if ($deleiverycomapny) {
            return response()->json(['status' => 'true', 'data' => "Delivery Company Found!", 'data' => $deleiverycomapny], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Find Delivery Company'], 403);
        }

    }
    public function getBrandsCompanies(Request $request)
    {
        $deleiverycomapny = DeliverCompany::get();
        $brands = Brands::get();
        if ($deleiverycomapny && $brands) {
            return response()->json(['status' => 'true', 'data' => "Data Found!", 'deleiverycomapny' => $deleiverycomapny, 'brands' => $brands], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Find Delivery Company'], 403);
        }

    }
    public function getProductById(Request $request, $id)
    {
        $product = Product::where('guid', $id)
            ->with('brand')
            ->with('category')
            ->with('user')
            ->with('shop')
            // ->with('shop')->withFeedBack
            ->first();
        foreach ($product->getAttributes() as $key => $value) {
            if ($value === null) {
                unset($product->$key);
            }
        }
        $product->brand = $product->brand;
        $product->category->parent_name="";
        if($product->category->parent_id != NULL && $product->category->parent_id != ""){
           $parent=Category::where('id', $product->category->parent_id)->first();
           $product->category->parent_name=$parent->name;

        }
        $test = json_decode(json_decode($product->attributes, true), true);
        $attributes = [];
        
    if (isset($test)) {
        $tempAttributes = [];

        foreach ($test as $te) {
            if (!isset($tempAttributes[$te['key']])) {
                $tempAttributes[$te['key']] = [];
            }
            
            if (is_array($te['value'])) {
                $tempAttributes[$te['key']] = array_merge($tempAttributes[$te['key']], $te['value']);
            } else {
                $tempAttributes[$te['key']][] = $te['value'];
            }
        }

        foreach ($tempAttributes as $key => $values) {
            $data = [
                "key" => $key,
                "options" => $values
            ];
            $attributes[] = $data;
        }
    } else {
        $attributes = [];
    }

        $product->attributes = $attributes;
        if($product->auctioned==1){
         $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
        }
        $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
        $sellerDataCount = FeedBack::where('product_id', $product->id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
        $feedbacks = FeedBack::where('product_id', $product->id)->get();
        $feedbacks_ = array();
        foreach ($feedbacks as $feedback) {
            $newDateString = date_format($feedback->created_at, "Y-m-d");
            // $month = $feedback->created_at->diffInMonths(Carbon::now());//Carbon::parse($newDateString)->diffInMonths(Carbon::now());
            // $months="";
            // if($month == 1){
            //     $months ="month";    
            // }
            // else if($month > 1){
            //     $months =$month ." month";    
            // }
            $data = [
                'id' => $feedback->id,
                'user' =>
                    [
                        'image' => $feedback->user->media[0]->url,
                        'name' => $feedback->user->name . '' . $feedback->user->lastname,
                        'period' => date_format($feedback->created_at, "Y-m-d")
                    ],
                'comments' => $feedback->comments,
                'ratings'=>$feedback->ratings,
                'productname' => $feedback->product->name
            ];
            array_push($feedbacks_, $data);
        }
        $feedback = [
            'count' => $sellerDataCount,
            'feedbacks' => $feedbacks_
        ];
        $sellerData_ = [
            'sellerName' => $sellerData->fullname,
            'sellerImage' => env('APP_URL') . $sellerData->cover_image,
            'positivefeedback' => 90,
            'feedback' => $feedback,
            'is_favourite' => $sellerData->is_favourite,
            'favourite_count' => $sellerData->favourite_count,
        ];
        $product->seller = $sellerData_;
        // $product->length=$product->length;
        // $product->width=$product->width;
        // $product->weight=$product->weight;
        // $product->height=$product->height;
        if ($product) {
            $product->parent_category_id=$product->category->parent_id;
            return response()->json(['status' => true, 'data' => $product], 200);
        } else {
            return response()->json(['status' => false, 'data' => "Unable To Get Product"], 400);
        }
    }

}
