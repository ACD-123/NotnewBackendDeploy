<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Follower;

class FavouriteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $auth = JWTAuth::user();
            if (!isset($auth->id)) {
                $auth = User::find($request->user_id);
            }
            $favourites = Favourite::getFavourites($auth->id, $request->type);
            $inStockProducts=array();
        $outOfStockProducts=array();
            if($request->type==1)
            {
            foreach($favourites as $fav)
            {
                $product=$fav->product;
                if($product->stockcapacity>0){
                if($product->recurring)
                {
                    $product->soldstatus='Sold Out';
                }
                else{
                    $product->soldstatus='Out of Stock';
                }
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
                     "favourite_count"=>$product->favourite_count
                    
                ];
                array_push($inStockProducts,$arr);
            }
            else{
                $product=$fav->product;
                if($product->recurring)
                {
                    $product->soldstatus='Sold Out';
                }
                else{
                    $product->soldstatus='Out of Stock';
                }
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
                    "bid_price"=>$product->bids
                ];
                array_push($outOfStockProducts,$arr);
            }
            }
            return response()->json([
                'success' => true,
                'data' => ["active"=>$inStockProducts,"in_active"=>$outOfStockProducts],
                'message' => 'Fetched Favourites Successfully!'
            ], 200);
            }
            return response()->json([
                'success' => true,
                'data' => $favourites,
                'message' => 'Fetched Favourites Successfully!'
            ], 200);

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
        $validate = Request()->validate([
            'favourite_against_id' => 'required',
            'user_id' => 'required',
            'type' => 'required'
        ]);
        try {
            $message = '';
            $fav = Favourite::where([
                ['user_id', $request->user_id],
                ['favourite_against_id', $request->favourite_against_id],
                ['type', $request->type]
            ])->first();
            if ($fav) {
                $fav->delete();
                $message = 'Favourite successfully removed.';
            } else {
                $fav = new Favourite;
                $fav->user_id = $request->user_id;
                $fav->favourite_against_id = $request->favourite_against_id;
                $fav->type = $request->type;
                if ($fav->save()) {
                    $message = 'Favourite successfully saved.';
                }
            }
            $is_favourite = Favourite::isFavourite($request->favourite_against_id, $request->type, $request->user_id);
            $total_favourite = Favourite::favouriteCount($request->favourite_against_id, $request->type);
            return response()->json([
                'success' => true,
                'data' => [
                        'is_favourite' => $is_favourite,
                        'total_favourite' => $total_favourite,
                    ],
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
    
      public function storeFollower(Request $request)
    {
        try {
            $message = '';

            $fav = Follower::where([
                ['follow_by', $request->user_id],
                ['follow_to', $request->follow_to]
            ])->first();
            if ($fav) {
                if($fav->is_follow==1)
                {
                    $fav->is_follow=0;
                    $fav->save();
                }
                else{
                    $fav->is_follow=1;
                    $fav->save();
                }
                $message = 'Follower Updated';
            } else {
                $fav = new Follower;
                $fav->follow_by = $request->user_id;
                $fav->follow_to = $request->follow_to;
                $fav->is_follow = 1;
                if ($fav->save()) {
                    $message = 'Follower Added!';
                }
            }
            $is_favourite = Follower::isFollowing($request->follow_to,$request->user_id);
            $total_favourite = Follower::getFollowersCount($request->follow_to);
            return response()->json([
                'success' => true,
                'data' => [
                        'is_favourite' => $is_favourite,
                        'total_favourite' => $total_favourite,
                    ],
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

    public function getFollowers(Request $request)
    {
        try {
            $auth = JWTAuth::user();
            if (!isset($auth->id)) {
                $auth = User::find($request->user_id);
            }
            $favourites = Follower::getFollowers($auth->id);
            return response()->json([
                'success' => true,
                'data' => $favourites,
                'message' => 'Fetched Followers Successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Something Went Wrong!'
            ], 400);
        }
    }

}