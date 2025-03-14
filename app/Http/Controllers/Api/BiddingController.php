<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bidding;
use App\Models\Product;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\SellerData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\FeedBack;
use App\Models\UserCart;
use App\Helpers\StripeHelper;
use App\Events\BidEvent;

class BiddingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $bids = Bidding::with('user')->where('product_id', $request->product_id)->where('seller_guid',$request->guid)->orderBy('bid_amount', 'desc')->take(5)->get();
            $product = Product::where('id', $request->product_id)
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
        $bidders=[];
        foreach($bids as $bid)
        {
            $bid->user->bid_id=$bid->id;
            $bid->user->media;
            $bid->user->time=$bid->created_at;
            $bid->user->bid_amount=$bid->bid_amount;
            array_push($bidders,$bid->user);
        }
            if ($bids) {
                return response()->json([
                    'success' => true,
                    'data' => ["product"=>$product,"bidders"=>$bidders],
                    'message' => 'Bids!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Bids Found!'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function getActiveInactiveAuctionedProducts(Request $request)
    {
        $user_id=$request->user_id;
        $seller=SellerData::where('user_id',$user_id)->first();
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
                ->where('user_id', '=', $user_id)
                ->where('products.auctioned', 1)      
                ->orderByDesc('products.featured')
                ->orderByDesc('products.created_at')
              
                ->get([
                    'categories.name as category',
                    'products.*'
                ]);
                $auctionProducts=[];
                $auctionInActiveProducts=[];
            foreach ($productAuctioned as $product) {
                 
                $product->seller_guid=$seller->guid;
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
                       $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
$currentTime = Carbon::now();
                    $auctionEndTime = Carbon::parse($product->auction_End_listing);
                    if ($auctionEndTime->lessThan($currentTime)) {
                        array_push($auctionInActiveProducts,$product);
                    }
                    else{
                        array_push($auctionProducts,$product);
                    }
            }
            
        
            
            return response()->json([
                    'success' => true,
                    'data' => ["active"=>$auctionProducts,"inactive"=>$auctionInActiveProducts],
                    'message' => 'Auctioned Products!'
                ], 200);
            
    }
    public function user(Request $request)
    {
        try {
            $finalBids=[];
            $bids = Bidding::with('user')->where('user_id', $request->user_id)->get();
            foreach ($bids as $bid) {
                $product = Product::where('id', $bid->product_id)
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
                          $currentTime = Carbon::now();
$auctionEndTime = Carbon::parse($product->auction_End_listing);
$remainingTimeInSeconds = $auctionEndTime->diffInSeconds($currentTime);
$product->auction_remainig_time = $remainingTimeInSeconds;
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
                     "max_bid"=>$product->max_bid,
                     "auction_remainig_time"=>$product->auction_remainig_time,
                     "description"=>$product->description
                    
                ];
                $bid->product=$arr;
                if ($auctionEndTime->gt($currentTime)) {
                    array_push($finalBids,$bid);
                }
            }
            
            if ($bids) {
                return response()->json([
                    'success' => true,
                    'data' => $finalBids,
                    'message' => 'Bids!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Bids Found!'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Something Went Wrong!'
            ], 400);
        }
    }
    public function store(Request $request)
    {
        try {
            $product = Product::find($request->product_id);
            if ($product) {
                if ($product->auctioned == 1) {
                    if($product->is_sold==1){
                        return response()->json([
                            'success' => false,
                            'data' => [],
                            'message' => 'Auction Ended!'
                        ], 400);
                    }
                    $currentTime = Carbon::now();
                    $auctionEndTime = Carbon::parse($product->auction_End_listing);
                    if ($auctionEndTime->lessThan($currentTime)) {
                        return response()->json([
                            'success' => false,
                            'data' => [],
                            'message' => 'Auction Ended!'
                        ], 400);
                    } 
                    if ($product->bids > $request->bid_value) {
                        return response()->json([
                            'success' => false,
                            'data' => [],
                            'message' => 'Your Bid Must be higher than ' . $product->bids
                        ], 400);
                    } else {
                        $bid = Bidding::where('user_id', $request->user_id)->where('product_id', $product->id)->first();
                        if ($bid) {
                            $bid_value = Bidding::getMaxBid($product->id);
                            $status = 0;
                            if ($request->bid_value > $bid_value) {
                                $bidding = Bidding::where('product_id', $product->id)->get();
                                if(count($bidding)>0)
                                {
                                    foreach ($bidding as $value) {
                                        $value->update([
                                            'status' => $status
                                        ]);
                                $notificationArray=[
                                    "title"=>"Bidding",
                                    "message"=>"You have lost the bidding war! Increase Your Bid!",
                                    "type"=>"auction",
                                    "user_id"=>$value->user_id,
                                    "sender_id"=>$product->user_id,
                                    "notification_type"=>"auction",
                                    "product_guid"=>$product->guid
                                ];
                                StripeHelper::saveNotification($notificationArray);
                                        
                                    }
                                }
                                
                                $bid->update([
                                    'status' => 1,
                                    'bid_amount' => $request->bid_value
                                ]);
                               $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have the highest bid Congratulations! The product will be awarded be awarded if nobody beats you in bidding war!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                        "product_guid"=>$product->guid
                                    ];
                                StripeHelper::saveNotification($notificationArray);
                                event(new BidEvent($product->id, $bid));                               
                            } else if ($request->bid_value == $bid_value) {
                                $bid->update([
                                    'status' => 0,
                                    'bid_amount' => $request->bid_value
                                ]);
                                $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have lost the bidding war! Increase Your Bid!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                        "product_guid"=>$product->guid
                                    ];
                                    StripeHelper::saveNotification($notificationArray);
                                    event(new BidEvent($product->id, $bid));
                            } else {
                                $bid->update([
                                    'status' => $status,
                                    'bid_amount' => $request->bid_value
                                ]);
                                $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have lost the bidding war! Increase Your Bid!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                            "product_guid"=>$product->guid
                                    ];
                                    StripeHelper::saveNotification($notificationArray);
                                    event(new BidEvent($product->id, $bid));
                            }
                            return response()->json([
                                'success' => true,
                                'data' => $bid,
                                'message' => "Bid Placed!"
                            ], 200);
                        } else {
                            $bid_value = Bidding::getMaxBid($product->id);
                            $status = 0;
                            if ($request->bid_value > $bid_value) {
                                $bidding = Bidding::where('product_id', $product->id)->get();
                                foreach ($bidding as $value) {
                                    $value->update([
                                        'status' => $status
                                    ]);
                                }
                                $sellerData = SellerData::where('id', $product->shop_id)->first();
                                $bid = Bidding::create([
                                    "user_id" => $request->user_id,
                                    "product_id" => $request->product_id,
                                    "status" => 1,
                                    "seller_guid" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
                                  $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have the highest bid Congratulations! The product will be awarded be awarded if nobody beats you in bidding war!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                            "product_guid"=>$product->guid
                                    ];
                                StripeHelper::saveNotification($notificationArray);
                                event(new BidEvent($product->id, $bid));
                                return response()->json([
                                    'success' => true,
                                    'data' => $bid,
                                    'message' => "Bid Placed!"
                                ], 200);
                            } else if ($request->bid_value == $bid_value) {
                                $sellerData = SellerData::where('id', $product->shop_id)->first();
                                $bid = Bidding::create([
                                    "user_id" =>$request->user_id,
                                    "product_id" => $request->product_id,
                                    "status" => 0,
                                    "seller_guid" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
                                $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have lost the bidding war! Increase Your Bid!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                        "product_guid"=>$product->guid
                                    ];
                                    StripeHelper::saveNotification($notificationArray);
                                    event(new BidEvent($product->id, $bid));
                                return response()->json([
                                    'success' => true,
                                    'data' => $bid,
                                    'message' => "Bid Placed!"
                                ], 200);
                            } else {
                                $sellerData = SellerData::where('id', $product->shop_id)->first();
                                $bid = Bidding::create([
                                    "user_id" =>$request->user_id,
                                    "product_id" => $request->product_id,
                                    "status" => 0,
                                    "seller_guid" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
                                $notificationArray=[
                                        "title"=>"Bidding",
                                        "message"=>"You have lost the bidding war! Increase Your Bid!",
                                        "type"=>"auction",
                                        "user_id"=>$bid->user_id,
                                        "sender_id"=>$product->user_id,
                                        "notification_type"=>"auction",
                                            "product_guid"=>$product->guid
                                    ];
                                    StripeHelper::saveNotification($notificationArray);
                                    event(new BidEvent($product->id, $bid));
                                return response()->json([
                                    'success' => true,
                                    'data' => $bid,
                                    'message' => "Bid Placed!"
                                ], 200);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'This Product is not in auction process!'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Product Found!'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function award(Request $request)
    {
        try {
            $bid=Bidding::find($request->id);
            if ($bid) {
                $product = Product::where('id', $bid->product_id)->first();
                $cart = new UserCart();
                $cart->product_id =  $bid->product_id;
                $cart->price =  $bid->bid_amount;
                $cart->quantity =  1;
                $cart->attributes = json_encode("[]");
                $cart->user_id =  $bid->user_id;
                $cart->shop_id =  $product->shop_id;
                $cart->is_auctioned=1;
               $cart->save();
               $product->update([
                "is_sold"=>1
               ]);
               $notificationArray=[
                "title"=>"Bidding",
                "message"=>"You have won the auction",
                "type"=>"auction",
                "user_id"=>$bid->user_id,
                "sender_id"=>$product->user_id,
                "notification_type"=>"auction",
                "product_guid"=>$product->guid,
                "win"=>1
            ];
        StripeHelper::saveNotification($notificationArray);
               Bidding::where('product_id',$bid->product_id)->delete();
                return response()->json([
                    'success' => true,
                    'data' => $cart,
                    'message' => 'Bids!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Bids Found!'
                ], 400);
            }
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
}