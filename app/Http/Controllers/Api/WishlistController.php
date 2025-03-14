<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\FeedBack;
use App\Models\SellerData;

class WishlistController extends Controller
{
    public function index(Request $request){
        $total = Wishlist::where('user_id', $request->user_id)->count();
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
        $wishlistArray = Wishlist::where('user_id', $request->user_id)->skip($skip)->take($page_size)->get();
        $wishlistProducts=array();
        $inStockProducts=array();
        $outOfStockProducts=array();
        foreach ($wishlistArray as $wish) {
            $product = Product::where('guid', $wish->product_guid)
                ->with('brand')
                ->with('category')
                ->with('user')
                ->with('shop')
                ->first();
            
            $sellerData = SellerData::with('feedback')->where('id', $product->shop_id)->first();
            $sellerDataCount = FeedBack::where('store_id', $product->shop_id)->count();//SellerData::with('feedback')->where('id', $product->shop_id)->count();
            $feedbacks = FeedBack::where('store_id', $product->shop_id)->get();
            $test = json_decode(json_decode($product->attributes, true), true);
            $attributes = [];
            if (isset($test)) {
                foreach ($test as $te) {
                    if (!isset($attributes[$te['key']])) {
                        $attributes[$te['key']] = [];
                    }
                    $attributes[$te['key']][] = $te['value'];
                }
            } else {
                $attributes = (object) [];
            }
            $product->attributes = $attributes;
            $feedbacks_ = array();
            foreach ($feedbacks as $feedback) {
                $data = [
                    'id' => $feedback->id,
                    'user' =>
                        [
                            'image' => $feedback->user->media[0]->name,
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
            if($product->stockcapacity>0){
                if($product->recurring)
                {
                    $product->soldstatus='Sold Out';
                }
                else{
                    $product->soldstatus='Out of Stock';
                }
                array_push($inStockProducts,$product);
            }
            else{
                if($product->recurring)
                {
                    $product->soldstatus='Sold Out';
                }
                else{
                    $product->soldstatus='Out of Stock';
                }
                array_push($outOfStockProducts,$product);
            }
            array_push($wishlistProducts,$product);
        }
        $data=[
            "in_stock_products"=>$inStockProducts,
            "out_of_stock_products"=>$outOfStockProducts,
            "pagination"=>$pagination
        ];

        return response()->json(['status'=> true,'data' => $data], 200);
    }
    public function store(Request $request)
    {
        $validate = Request()->validate([
            'product_guid' => 'required',
            'user_id' => 'required',
        ]);
        try {
            $message = '';
            $fav = Wishlist::where([
                ['user_id', $request->user_id],
                ['product_guid', $request->product_guid],
            ])->first();
            if ($fav) {
                $fav->delete();
                $message = 'Product successfully removed from wishlist.';
            } else {
                $product = Product::where('guid', $request->product_guid)->first();
                $fav = new Wishlist;
                $fav->user_id = $request->user_id;
                $fav->product_guid = $request->product_guid;
                $fav->product_id = $product->id;
                if ($fav->save()) {
                    $message = 'Product  successfully saved in wishlist.';
                }
            }
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => $message
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ],400);
        }
    }
}
