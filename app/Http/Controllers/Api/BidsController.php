<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Helpers\GuidHelper;
use App\Models\Bids;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use App\Notifications\BidAccepted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;



class BidsController extends Controller
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
        return DB::transaction(function () use ($request) { 
            $product = Product::where('guid', $request->get('id'))->first();
            $maxBids = Bids::where('product_id', $product->id)->max('max_bids');
            // return [
            //     'get-bids' => (int)$request->get('max_bids'),
            //     'max-bids' => $maxBids,
            // ];
            // $getBids = $request->get('max_bids');
            // if((int)$getBids < (int)$maxBids){
            //     return response()->json(['status'=>'prohibited','data'=>'Your Bid is less then Already Bid'], 200);
            // }else{
                Bids::where('product_id', $product->id)
                ->where('user_id', \Auth::user()->id)
                ->delete();
                $bids = new Bids();
                $bids->max_bids = $maxBids ? $maxBids : 0;
                $bids->shipping_charges = $request->get('shipping_charges');
                $bids->time_bids = $request->get('time_bids');
                $bids->estimated_total = $request->get('estimated_total');
                $bids->user_id = \Auth::user()->id;
                $bids->product_id = $product->id;
                $bids->guid = GuidHelper::getGuid();
                $bids->save();
                if($bids){
                    return response()->json(['status'=>'true','data'=>$bids],200);
                }else{
                    return response()->json(['status'=>'false','message'=>'Unable to Place Bid!'],403);
                }
            // }
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
    /**
     * Get Maximum Bids
     */
    public function getMaxBids($id)
    {
        $bids = Bids::get();
        if(count($bids) > 0){
            $product = Product::where('guid',$id)->first();
            // return Bids::where('user_id', \Auth::user()->id)
            return Bids::where('product_id', $product->id)
            ->max('max_bids');
        }else{
            return '0';
        }   
    }

    public function getProductBids($id)
    {
        $product = Product::where('guid',$id)->first();
        $bids = Bids::where('product_id', $product->id)->count();      
        if($bids > 0){
            return $bids;
        }else{
            return 0;
        }
    }
    
    public function getTotalBidsProduct($id)
    {
        $product = Product::where('guid',$id)->first();
        $bids = Bids::where('product_id', $product->id)
            ->with('product')
            ->with('user')
            ->get();
        $data =[];      
        foreach($bids as $bid){
            array_push($data, $bid->max_bids);
        }
        $totalBids = array_sum($data);

        if($totalBids){
            return response()->json(['status'=> true,'data' =>$totalBids], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Bids of this Product!"], 200);       
        }
    }
    public function getUserBids()
    {
        $bids = Bids::where('user_id', \Auth::user()->id)
            ->with('product')
            ->with('user')
            ->get();
        if($bids){
            return response()->json(['status'=> true,'data' =>$bids], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Bids of this Product!"], 400);       
        }
    }

    public function getBidsUserProduct($id)
    {
        $product = Product::where('guid',$id)->first();
        if($product){
            $bids = Bids::where('product_id', $product->id)
            ->where('user_id', \Auth::user()->id)
            ->with('product')
            ->with('user')
            ->first();
            if($bids){
                return response()->json(['status'=> true,'data' =>$bids], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"Unable To Get Bids of this Product!"], 400);       
            }
        }
    }

    public function confirmedBids(Request $request){
        
        $product = Product::where('guid', $request->get("product_id"))->first();
        
        $bids = Bids::where('product_id', $product->id)->update([
            'confirmed' => true
        ]);

        if($bids){
            return response()->json(['status'=> true,'data' =>'Your Bid has been Confirmed!'], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Seller"], 400);       
        }
    }
    public function acceptBid(Request $request){
        
        return DB::transaction(function () use ($request) {         

                $bids = Bids::where('user_id', $request->get('user_id'))
                    ->where('product_id', $request->get('product_id'))
                    ->update([
                        'reject' => false,
                        'accept' => true,
                        'status' => 'accepted'
                    ]);
                $bid = Bids::where('user_id', $request->get('user_id'))
                ->where('product_id', $request->get('product_id'))
                ->first();
                if($bids){
                    $user = User::where('id', $request->get('user_id'));
                    //MAIL on live server
                    return response()->json(['status'=> true,'data' =>'Bid has been Accepted!'], 200);       
                }else{
                    return response()->json(['status'=> false,'data' =>"Unable to Accept Bid!"], 400);       
                }
            });
    }
    public function rejectBid(Request $request){
        $bids = Bids::where('user_id', $request->get('user_id'))
            ->where('product_id', $request->get('product_id'))
            ->update([
                'reject' => true,
                'accept' => false,
                'status' => 'rejected'
            ]);
        if($bids){
            return response()->json(['status'=> true,'data' =>'Bid has been Rejected!'], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Reject a Bid!"], 400);       
        }
    }
    public function getSellerActiveBid(Request $request){
        //     $productIds = [];
        //     foreach($bids as $bid){
        //         array_push($productIds, $bid->product_id);   
        //     }
        //     $productId = array_unique($productIds);
        //     $products = Product::
        //     whereIn('id',$productId)
        //     ->where('user_id', \Auth::user()->id)
        //     ->get();
        // return $products;
        // die();
        $bids = Bids::where('status','pending')
        ->get();
        $data = "";
        $getData = [];
        foreach($bids as $bid){
            $product = Product::where('id', $bid->product_id)
                ->where('user_id', \Auth::user()->id)
                ->first();
            if($product){
                $bids = Bids::where('product_id', $product->id)
                ->orderBy('created_at', 'desc')->first();
                $bidsNo = Bids::where('product_id', $product->id)->count();
                $totalBids = Bids::where('product_id', $product->id)
                ->with('user')
                ->with('product')
                ->orderBy('created_at', 'desc')->get();
                $data =[
                    'product' => $product,
                    'currentbid'=>$bids,
                    'bidsno'=>$bidsNo,
                    'endsIn'=>$product->auction_listing, 
                    'totalbids'=> $totalBids
                ];    
                array_push($getData, $data);
                }
        }
        if($getData){
            return response()->json(['status'=> true,'data' =>$getData], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Bids"], 200);       
        }
        
    }
}
