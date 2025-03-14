<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\SellerData;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\UserCart;
use Validator;
use App\Models\UserStoreVouchers;
use Auth;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        try {
            $auth = JWTAuth::user();
            if (!isset($auth->id)) {
                $auth = User::find($request->user_id);
            }
            $seller = SellerData::where('user_id', $request->user_id)->first();
            $activeCoupons = array();
            $expiredCoupons = array();
            $coupons = Coupon::where('seller_guid', $seller->guid)->where('title', 'LIKE', '%' . $request->search_key . '%')->where('is_deleted', 0)->get();
            $currentDate = date("Y-m-d", strtotime($request->user_date));
            foreach ($coupons as $coupon) {
                $endDate = date("Y-m-d", strtotime($coupon->end_date));
                if ($currentDate >= $endDate) {
                    array_push($expiredCoupons, $coupon);
                } else {
                    array_push($activeCoupons, $coupon);
                }
            }
            $data = [
                "active_coupons" => $activeCoupons,
                "expired_coupons" => $expiredCoupons,
                "active_count" => count($activeCoupons),
                "expired_count" => count($expiredCoupons),
            ];
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Coupons List!'
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
            'title' => 'required',
            'discount' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'min_order' => 'required',
            "seller_guid" => "required",
            "code" => "required"
        ]);
        try {

            Coupon::create([
                "title" => $request->title,
                "code" => $request->code,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "discount" => $request->discount,
                "min_order" => $request->min_order,
                "seller_guid" => $request->seller_guid
            ]);
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Discount Coupon Created Successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function update(Request $request)
    {
        // $validate = Request()->validate([
        //     'id' => 'required',
        //     'title' => 'required',
        //     'discount' => 'required',
        //     'discount_type' => 'required',
        //     'discount_usage' => 'required',
        //     'status' => "required"
        // ]);
        try {
            $coupon = Coupon::find($request->id);
            if ($coupon) {
                $coupon->update([
                    "title" => $request->title,
                    "code" => $request->code,
                    "start_date" => $request->start_date,
                    "end_date" => $request->end_date,
                    "discount" => $request->discount,
                    "min_order" => $request->min_order,
                    "seller_guid" => $request->seller_guid
                ]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Discount Coupon Updated Successfully!'
                ], 200);
            } else {

                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this ID!'
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
    public function deleteCoupon(Request $request)
    {
        try {
            $coupon = Coupon::find($request->id);
            if ($coupon) {
                $coupon->update([
                    "is_deleted" => 1
                ]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Discount Coupon Deleted Successfully!'
                ], 200);
            } else {

                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this ID!'
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
    public function updateStatus(Request $request)
    {
        try {
            $coupon = Coupon::find($request->id);
            if ($coupon) {
                $coupon->update([
                    "status" => $request->status,
                ]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Discount Coupon Status Updated Successfully!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this ID!'
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
    public function getById($id)
    {
        try {
            $coupon = Coupon::find($id);
            if ($coupon) {
                return response()->json([
                    'success' => true,
                    'data' => $coupon,
                    'message' => 'Discount Coupon!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this ID!'
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
    public function checkCoupon(Request $request)
    {
        try {
            $couopn_code = $request->coupon_code;
            $userId = $request->user_id;
            $userDate = $input['date'];
            $userCart = UserCart::where('user_id', $userId)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
            $total=array();
            foreach($userCart as $cart){
                array_push($total, $cart->price);
            }
            $orderTotal = array_sum($total);
            $coupon = Coupon::where('code', $couopn_code)->first();
            if ($coupon) {
                $endDate = date("Y-m-d", strtotime($coupon->end_date));
                $currentDate = date("Y-m-d", strtotime($userDate));
                $seller = SellerData::where('guid', $coupon->seller_guid)->first();
                if($seller->user_id==$userId)
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "You can't use the coupon that you have created!"
                    ], 400);
                }
                else if ($currentDate >= $endDate) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Coupon Expired!'
                    ], 400);
                }
                else if($coupon->is_deleted==1)
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "Coupon Code doesn't exist!"
                    ], 400);
                }
                else if($coupon->min_order>$orderTotal){
                    $lessValue=$coupon->min_order-$orderTotal;
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Min Order Value should be '.$coupon->min_order.'. Please add '.$lessValue.' more worth of items to avail the discount!'
                    ], 400);
                }
                else{
                    foreach($userCart as $cart){
                        $cart->update([
                            "coupon_code"=>$couopn_code
                        ]);
                    }
                    return response()->json([
                        'success' => true,
                        'data' => $userCart,
                        'message' => 'Discount Coupon Applied!'
                    ], 200);
                }
            } else {

                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this code!'
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
    
       public function checkCouponGuest(Request $request)
    {
        try {
            $couopn_code = $request->coupon_code;
            $userId = $request->user_id;
            $userDate = $input['date'];
            $userCart = UserCart::where('guest_user_id', $userId)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
            $total=array();
            foreach($userCart as $cart){
                array_push($total, $cart->price);
            }
            $orderTotal = array_sum($total);
            $coupon = Coupon::where('code', $couopn_code)->first();
            if ($coupon) {
                $endDate = date("Y-m-d", strtotime($coupon->end_date));
                $currentDate = date("Y-m-d", strtotime($userDate));
                $seller = SellerData::where('guid', $coupon->seller_guid)->first();
                if($seller->user_id==$userId)
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "You can't use the coupon that you have created!"
                    ], 400);
                }
                else if ($currentDate >= $endDate) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Coupon Expired!'
                    ], 400);
                }
                else if($coupon->is_deleted==1)
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "Coupon Code doesn't exist!"
                    ], 400);
                }
                else if($coupon->min_order>$orderTotal){
                    $lessValue=$coupon->min_order-$orderTotal;
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Min Order Value should be '.$coupon->min_order.'. Please add '.$lessValue.' more worth of items to avail the discount!'
                    ], 400);
                }
                else{
                    foreach($userCart as $cart){
                        $cart->update([
                            "coupon_code"=>$couopn_code
                        ]);
                    }
                    return response()->json([
                        'success' => true,
                        'data' => $userCart,
                        'message' => 'Discount Coupon Applied!'
                    ], 200);
                }
            } else {

                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No Coupon against this code!'
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
    public function deleteCouponCart(Request $request)
    {
        try {
            $userCart = UserCart::where('user_id', $request->user_id)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
            foreach($userCart as $cart){
                $cart->update([
                    "coupon_code"=>NULL
                ]);
            }
            return response()->json([
                'success' => true,
                'data' => $userCart,
                'message' => 'Discount Coupon Removed!'
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
     public function deleteCouponCartGuest(Request $request)
    {
        try {
            $userCart = UserCart::where('guest_user_id', $request->user_id)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
            foreach($userCart as $cart){
                $cart->update([
                    "coupon_code"=>NULL
                ]);
            }
            return response()->json([
                'success' => true,
                'data' => $userCart,
                'message' => 'Discount Coupon Removed!'
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateVideo(Request $request)
    {
        $seller = SellerData::where('user_id', Auth::user()->id)->first();
        if($seller){
            $video=$seller->video;
            if($request->deleted==1){
                $seller->update([
                    "video"=>""
                ]);
            }
            if($request->hasFile('video')){
                $imageName = time().'-'.$request->file('video')->getClientOriginalName();
                $extension = $request->file('video')->getClientOriginalExtension();
                $destinationPath = public_path('/image/category/');
                $request->file('video')->move($destinationPath, $imageName);
                $video='image/category/'.$imageName;
            }
            $seller->update([
                "video"=>$video
            ]);
            
            return response()->json(['status'=> true,'data' =>[],'message'=>"Seller Updated!"], 200);
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Seller"], 200);       
        }
    }

    public function deleteCouponStores(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'user_id' => 'required',
                'store_id' => 'required',
                'coupon_code' => 'required'
            ];
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data' => [$validator->errors()],
                    'message' => "Validation Error"
                ], 422);
            }
            $voucherApply=UserStoreVouchers::where('user_id',$input['user_id'])->where('store_id',$input['store_id'])->where('coupon_code',$input['coupon_code'])->whereNull('order_id')->first();
            if($voucherApply){
                $voucherApply->delete();
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => "Coupon Code Deleted!"
                ], 200);
            }
            else{
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => "Coupon Code Not Found!"
                ], 400);
            }
        }
        catch(\Exception $e)
        {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkCouponStores(Request $request)
    {
        try {
            $input = $request->all();
            $rules = [
                'user_id' => 'required',
                'store_id' => 'required',
                'coupon_code' => 'required',
                'date'=>'required'
            ];

            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data' => [$validator->errors()],
                    'message' => "Validation Error"
                ], 422);
            }
            $userCart = UserCart::where('user_id', $input['user_id'])->where('shop_id',$input['store_id'])->get();
            $total=0;
            foreach ($userCart as $value) {
                $total+=$value->price;
            }
            $seller = SellerData::find($input['store_id']);
            if(!$seller){
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => "Seller Not Found!"
                ], 400);
            }
            $userDate=$input['date'];
            $checkStoreVoucher=UserStoreVouchers::where('user_id',$input['user_id'])->where('store_id',$input['store_id'])->where('coupon_code','=',$input['coupon_code'])->first();
            if($checkStoreVoucher)
            {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => "You have already redeemed this voucher!"
                ], 400);
            }
            $coupon = Coupon::where('code', $input['coupon_code'])->where('seller_guid',$seller->guid)->first();
            if ($coupon) {
                $endDate = date("Y-m-d", strtotime($coupon->end_date));
                $currentDate = date("Y-m-d", strtotime($userDate));
                if($seller->user_id==$input['user_id'])
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "You can't use the coupon that you have created!"
                    ], 400);
                }
                else if ($currentDate >= $endDate) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Coupon Expired!'
                    ], 400);
                }
                else if($coupon->is_deleted==1)
                {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => "Coupon Code doesn't exist!"
                    ], 400);
                }
                else if($coupon->min_order>$total){
                    $lessValue=$coupon->min_order-$total;
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Min Order Value should be '.$coupon->min_order.'. Please add '.$lessValue.' more worth of items to avail the discount!'
                    ], 400);
                }
                else{
                    $voucherApply=UserStoreVouchers::where('user_id',$input['user_id'])->where('store_id',$input['store_id'])->whereNull('order_id')->first();
                    if($voucherApply)
                    {
                        $voucherApply->update([
                            "coupon_code"=>$input['coupon_code']
                        ]);
                        return response()->json([
                            'success' => true,
                            'data' => [],
                            'message' => "Voucher Applied!"
                        ], 200);
                    }
                    else
                    {
                        UserStoreVouchers::create([
                            "user_id"=>$input['user_id'],
                            "store_id"=>$input['store_id'],
                            "coupon_code"=>$input['coupon_code'],
                        ]);
                        return response()->json([
                            'success' => true,
                            'data' => [],
                            'message' => "Voucher Applied!"
                        ], 200);
                    }
                }
            }
            else{
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => "Coupon Not Found!"
                ], 400);
            }
        }
        catch(\Exception $e)
        {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
