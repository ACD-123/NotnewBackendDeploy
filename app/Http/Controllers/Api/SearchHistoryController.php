<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SearchHistory;
use App\Models\SearchHistoryProduct;
use App\Models\SellerData;
use App\Models\FeedBack;
use App\Models\User;
use Carbon\Carbon;

class SearchHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user_id ?? null;
        $search_key = $request->search_key;
        if (!empty($user_id)  && $user_id!="undefined" && $user_id!="null") {
             $total = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('underage',1)->where('stockcapacity', '>', 0)->where('user_id','!=',$user_id)->where('is_deleted', 0)->count();
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
        $items = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('stockcapacity', '>', 0)->where('underage',1)->where('user_id','!=',$user_id)->where('is_deleted', 0)->skip($skip)->take($page_size)->get();
        $dataProducts=[];
    foreach($items as $item){
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
                     "underage"=> $item->underage
                    
                ];
                $dataProducts[]=$arr;
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
                 "underage"=> $item->underage
                
            ];
            $dataProducts[]=$arr;
        }
        
                
    }
        $data = [
            "products" => $dataProducts,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
            
        }
        else{
              $total = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('underage',1)->where('stockcapacity', '>', 0)->where('is_deleted', 0)->count();
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
        $items = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('stockcapacity', '>', 0)->where('underage',1)->where('is_deleted', 0)->skip($skip)->take($page_size)->get();
           $dataProducts=[];
    foreach($items as $item){
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
                     "underage"=> $item->underage
                    
                ];
                $dataProducts[]=$arr;
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
                 "underage"=> $item->underage
                
            ];
            $dataProducts[]=$arr;
        }
                
    }
        $data = [
            "products" => $dataProducts,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
        }
        
      
    }
    
    public function indexUnderAge(Request $request)
    {
        $user_id = $request->user_id ?? null;
        $search_key = $request->search_key;
        $user=User::find($user_id);
        
        if (!empty($user_id)  && $user_id!="undefined" && $user_id!="null") {
             $total = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('underage',0)->where('is_deleted', 0)->where('stockcapacity', '>', 0)->where('user_id','!=',$user_id)->count();
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
        $items = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('is_deleted', 0)->where('stockcapacity', '>', 0)->where('underage',0)->where('user_id','!=',$user_id)->skip($skip)->take($page_size)->get();
        
          $dataProducts=[];
    foreach($items as $item){
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
                     "underage"=> $item->underage
                    
                ];
                $dataProducts[]=$arr;
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
                 "underage"=> $item->underage
                
            ];
            $dataProducts[]=$arr;
        }
    }
        
        $data = [
            "products" => $dataProducts,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
            
        }
        else{
                 $total = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('is_deleted', 0)->where('underage',0)->where('stockcapacity', '>', 0)->count();
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
        $items = Product::where('name', 'LIKE', '%' . $search_key . '%')->where('is_sold', 0)->where('active', 1)->where('is_deleted', 0)->where('stockcapacity', '>', 0)->where('underage',0)->skip($skip)->take($page_size)->get();
        
          $dataProducts=[];
    foreach($items as $item){
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
                     "underage"=> $item->underage
                    
                ];
                $dataProducts[]=$arr;
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
                 "underage"=> $item->underage
                
            ];
            $dataProducts[]=$arr;
        }
    }
        
        $data = [
            "products" => $dataProducts,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
        }
        
      
    }
    public function storeSearchKeyword(Request $request)
    {
        $userId = $request->user_id;
        $existingKeyword = SearchHistory::where('user_id', $userId)->where('keyword', $request->keyword)->first();
        if (!$existingKeyword) {
            // Store the keyword for the user
            $existingKeyword = SearchHistory::create([
                'user_id' => $userId,
                'keyword' => $request->keyword,
            ]);
        }
        return response()->json(['status' => true, 'data' => $existingKeyword], 200);
    }

    public function storeSearchKeywordProducts(Request $request)
    {
        $userId = $request->user_id;
        $existingKeyword = SearchHistoryProduct::where('user_id', $userId)->where('product_id', $request->product_id)->first();
        if (!$existingKeyword) {
            // Store the keyword for the user
            $existingKeyword = SearchHistoryProduct::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
            ]);
        }
        return response()->json(['status' => true, 'data' => $existingKeyword], 200);
    }
    public function getStoreProductsFromSearch(Request $request)
    {
        $userId = $request->user_id;
        $total = SearchHistoryProduct::where('user_id', $userId)->count();
        $page = $request->page ?? 1;
        $page_size = $request->page_size ?? 20;
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
        $searchHistoryList = SearchHistoryProduct::where('user_id', $userId)->get();
        $productsArray = array();
        foreach ($searchHistoryList as $list) {
            $product = Product::where('id', $list->product_id)
            ->where('is_deleted', 0)
                ->with('brand')
                ->with('category')
                ->with('user')
                ->with('shop')
                ->first();
            foreach ($product->getAttributes() as $key => $value) {
                if ($value === null) {
                    unset($product->$key);
                }
            }
            $product->search_id=$list->id;
            $product->brand = $product->brand;
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
            $feedback=[
                'count'=>$sellerDataCount,
                'feedbacks'=> $feedbacks_
                ];
                $sellerData_=[
                    'sellerName' => $sellerData->fullname,
                    'sellerImage' => env('APP_URL').$sellerData->cover_image,
                    'positivefeedback'=> 90,
                    'feedback'=>$feedback,
                    'is_favourite'=>$sellerData->is_favourite,
                    'favourite_count'=>$sellerData->favourite_count,
                    ];
            $product->seller = $sellerData_;
            $arr=[
                    "id"=>$product->id,
                    "guid"=>$product->guid,
                    "name"=>$product->name,
                    "auctioned"=>$product->auctioned,
                    "price"=>$product->price,
                    "sale_price"=>$product->sale_price,
                    "media"=>$product->media,
                     "bid_price"=>$product->bids,
                     "bids"=>$product->bids,
                          "is_favourite"=>$product->is_favourite,
                     "favourite_count"=>$product->favourite_count,
                     "description"=>$product->description,
                      "search_id"=>$product->search_id
                    
                ];
            array_push($productsArray, $arr);
        }
        $items=collect($productsArray)->skip($skip)->take($page_size)->all();
        
        $data = [
            "products" => $items,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
    }
    public function clearSeacrch(Request $request)
    {
        $userId = $request->user_id;
        SearchHistory::where('user_id', $userId)->delete();
        SearchHistoryProduct::where('user_id', $userId)->delete();
        return response()->json(['status' => true, 'data' => []], 200);
    }
    public function deleteSearch(Request $request)
    {
        SearchHistory::where('id', $request->id)->delete();
        return response()->json(['status' => true, 'data' => []], 200);
    }
      public function deleteSearchProduct(Request $request)
    {
        SearchHistoryProduct::where('id', $request->id)->delete();
        return response()->json(['status' => true, 'data' => []], 200);
    }
    public function getSearchKeyWordsList(Request $request)
    {
        $total = SearchHistory::where('user_id',$request->user_id)->count();
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
        $items = SearchHistory::where('user_id',$request->user_id)->skip($skip)->take($page_size)->get();
        $data = [
            "searches" => $items,
            "pagination" => $pagination
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
    }
}
