<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Helpers\StripeHelper;
use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Models\Product;
use App\Models\Refund;
use App\Models\UserOrder;
use App\Models\Media;
use App\Models\Fedex;
use App\Models\FeedBack;
use App\Models\ShippingDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stripe\StripeClient;
use Carbon\Carbon;
use App\Images;
use Image;

class FeedBackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = Product::where('id', $request->get('product_id'))->first();
        $feedback=FeedBack::where('product_id',$request->get('product_id'))->where('user_id', \Auth::user()->id)->where('store_id',$product->shop_id)->first();
        if($feedback)
        {
            return response()->json(['status'=>'false','message'=>'Feedback Already Provided!'],403);
        }
        return DB::transaction(function () use ($request) {
            $product = Product::where('id', $request->get('product_id'))->first();
            $feedback = new FeedBack();
            $feedback->product_id = $request->get('product_id');
            $feedback->user_id = \Auth::user()->id;
            $feedback->store_id =$product->shop_id;
            $feedback->comments = $request->get('comments');
            $feedback->is_positive=$request->get('is_positive')??1;
            $feedback->ratings=$request->get('rating');
            $feedback->save();
            if($feedback){
                return response()->json(['status'=>'true','data'=>"Feed Back has been Created!!"],200);
            }else{
                return response()->json(['status'=>'false','message'=>'Unable to Create Feed Back!'],403);
            }
        });
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function getByStoreId($id){
        
    }
}
