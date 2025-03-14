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

class BiddingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $bids = Bidding::with('user')->where('product_id', $request->product_id)->where('seller_guid', $request->seller_guid)->get();
            foreach ($bids as $bid) {
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
                if ($product->auctioned == 1) {
                    $currentTime = Carbon::now();
                    $auctionEndTime = Carbon::parse($product->auction_End_listing);
                    $remainingTimeInSeconds = $auctionEndTime->diffInRealMicroseconds($currentTime, false);
                    $product->auction_remainig_time = $remainingTimeInSeconds * 1000;
                }
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
                $bid->product=$product;
            }
            if ($bids) {
                return response()->json([
                    'success' => true,
                    'data' => $bids,
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
    public function user(Request $request)
    {
        try {
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
                if ($product->auctioned == 1) {
                    $currentTime = Carbon::now();
                    $auctionEndTime = Carbon::parse($product->auction_End_listing);
                    $remainingTimeInSeconds = $auctionEndTime->diffInRealMicroseconds($currentTime, false);
                    $product->auction_remainig_time = $remainingTimeInSeconds * 1000;
                }
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
                $bid->product=$product;
            }
            if ($bids) {
                return response()->json([
                    'success' => true,
                    'data' => $bids,
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
                                foreach ($bidding as $value) {
                                    $value->update([
                                        'status' => $status
                                    ]);
                                }
                                $bid->update([
                                    'status' => 1,
                                    'bid_amount' => $request->bid_value
                                ]);
                            } else if ($request->bid_value == $bid_value) {
                                $bid->update([
                                    'status' => 1,
                                    'bid_amount' => $request->bid_value
                                ]);
                            } else {
                                $bid->update([
                                    'status' => $status,
                                    'bid_amount' => $request->bid_value
                                ]);
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
                                    "seller_id" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
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
                                    "status" => 1,
                                    "seller_id" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
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
                                    "seller_id" => $sellerData->guid,
                                    "bid_amount" => $request->bid_value
                                ]);
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
                $cart->attributes = '[]';
                $cart->user_id =  $bid->user_id;
                $cart->shop_id =  $product->shop_id;
                $cart->is_auctioned=1;
               $cart->save();
               $product->update([
                "is_sold"=>1
               ]);
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