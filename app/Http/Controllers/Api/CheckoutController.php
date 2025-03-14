<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\GuidHelper;
use App\Models\CheckOut;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SellerData;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserStoreVouchers;
use App\Models\Coupon;


class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CheckOut::with(['user'])
        ->with(['cart'])
        ->get();

    }
    
    public function selfCheckOut_()
    {
        try {
            $saleDiscountValue=0;
            $checkout = CheckOut::where('user_id', \Auth::user()->id)->first();
            $sellerData = SellerData::where('id', $checkout->store_id)->first(['id', 'fullname']);
            $product = Product::where('id', $checkout->product_id)->first();
            $weightTotal=$product->weight * $checkout->quantity;
            $productPrice=0;
            if($product->sale_price>0 && $product->sale_price!=NULL)
            {
                $saleDiscountValue=$product->price-$product->sale_price;
                $saleDiscountValue=$saleDiscountValue*$checkout->quantity;
                $productPrice=$product->sale_price;
                $checkout->sub_total = $product->sale_price;
            }
            else{
                $checkout->sub_total = $product->price;
                $productPrice=$product->price;
            }
            
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'underage'=> $product->underage,
                'price'=>$product->price,
                'originalPrice' => $productPrice,
                'buynowprice' => $productPrice * $checkout->quantity,
                'attributes' => json_decode(json_decode($checkout->attri)),
                'media' => $product->media,
                'buynowquantity' => $checkout->quantity
                
            ];
            $sellerdata = [
                "storeid" => $sellerData->id,
                "storename" => $sellerData->fullname,
                'products' => [$data],

            ];

            //         return $sellerdata;
            //         die();
            // // $checkoutData =  SellerData::where('id', $checkout->store_id)
            // // ->with('products')
            // // ->first();
            $product = Product::where('id', $checkout->product_id)->first();
            $shippingTotal = $product->shipping_price;
            $sellData = array();
            $sellDatas = array_push($sellData, $sellerdata);
            if ($sellerdata) {
                return response()->json([
                    'success' => true,
                    'sub_total'=>$checkout->order_total+$saleDiscountValue,
                    'total' => $checkout->order_total,
                    'discount'=>$saleDiscountValue,
                    'shipping' => $shippingTotal,
                    'weight'=>$weightTotal,
                    'data' => $sellData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to Fetch Data'
                ],400);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to Fetch Data'
            ],400);
        }
    }
    public function selfCheckOut(){
       
        $checkout = CheckOut::where('user_id', \Auth::user()->id)->first();
        $checkoutData = SellerData::where('id', $checkout->store_id)->with(['products' => function ($query) {
            $checkout = CheckOut::where('user_id', \Auth::user()->id)->first();
            $query->where('id', $checkout->product_id);
        }])->first();
        // $checkoutData =  SellerData::where('id', $checkout->store_id)
        // ->with('products')
        // ->first();
        $product = Product::where('id', $checkout->product_id)->first();
        $shippingTotal = $product->shipping_price;
      if($checkoutData){
            return response()->json([
                    'success'=> true,
                    'total'=> $checkout->order_total + $shippingTotal,                    
                    'shipping'=> $shippingTotal,
                    'data'=> $checkoutData,
                ]);  
       }else{
           return response()->json([
                    'success'=> false,
                    'message' => 'Failed to Fetch Data'
                ]);
       }
    }
    public function self()
    {
        
        // return CheckOut::with(['user'])
        // ->with(['cart'])
        // ->get();
        $userCart = UserCart::where('user_id', \Auth::user()->id)->get();
        $shop=array();
        foreach($userCart as $cart){
            // array_push($shopId, $cart->shop_id);
          $sellerdata =  SellerData::where('id', $cart->shop_id)->with(['products' => function ($query) {
              $cart = UserCart::where('user_id', \Auth::user()->id)->get();
              $productId = array();
              foreach($cart as $ct){
                  array_push($productId, $ct->product_id);
              }
                $query->whereIn('id', $productId);
            }])->first();
            
            array_push($shop, $sellerdata);
        }
        $productId=array();
        foreach($userCart as $cart){
            array_push($productId, $cart->product_id);
        }
        $products = Product::whereIn('id', $productId)->get();
        foreach($products as $pro){
            $cart = UserCart::where('user_id', \Auth::user()->id)->where('product_id',$pro->id)->first();
            $pro->price= $cart->price;
            $pro->quantity=$cart->quantity;
        }
        
        $shipping = array();
        foreach($products as $pro){
            array_push($shipping, $pro->shipping_price);
        }
        $shippingTotal = array_sum($shipping);
        
        $total=array();
        foreach($userCart as $cart){
            array_push($total, $cart->price);
        }
        $orderTotal = array_sum($total);
        $totalOrder = $orderTotal + 0;

        $shopData = array_unique($shop);
        $collection = array_values($shopData);
        
       if($shop){
            return response()->json([
                    'success'=> true,
                    'total'=> $totalOrder,                    
                    'shipping'=> 0,
                    'data'=> $collection,
                ]);  
       }else{
           return response()->json([
                    'success'=> false,
                    'message' => 'Failed to Fetch Data'
                ]);
       }
    }
    public function self_()
    {
         $userCart = UserCart::where('user_id', \Auth::user()->id)->get();
         $sellerDatas=[];
         $totalDiscountValue=0;
         $saleDiscountValue=0;
         foreach($userCart as $cart){
            $sellerData =SellerData::where('id', $cart->shop_id)->first(['id','fullname']);
            array_push($sellerDatas, $sellerData);
              
         }
         $uniqueSeller = array_unique($sellerDatas);
         $uniquesellss = [];
         foreach($uniqueSeller as $uniquesell){
            $voucherDiscount=0;
            $uCart = UserCart::where('user_id', \Auth::user()->id)
                ->where('shop_id', $uniquesell->id)
            ->get();
            $voucher = UserStoreVouchers::where('user_id', \Auth::user()->id)
            ->where('store_id', $uniquesell->id)
            ->whereNull('order_id')
            ->first();
            $productId = array();
            foreach($uCart as $ct){
                 $products = Product::where('shop_id', $ct->shop_id)
                 ->where('id', $ct->product_id)
                 ->get(['id', 'name', 'price','stockcapacity']);
                 $productDtl = array();
                 foreach($products as $product){
                     $price="";
                     $quantity ="";
                     if($ct->product_id === $product->id){
                        $price= $ct->price;    
                        $quantity= $ct->quantity; 
                     }
                     $data = [
                         'id' => $product->id,
                         'name'=> $product->name,
                         'originalPrice'=> $product->price,
                         'cartprice'=> $price,
                         'attributes'=>json_decode(json_decode($ct->attributes,true)),
                         'stockquantity'=>$product->stockcapacity,
                         'media' =>$product->media,
                         'cartquantity'=> $quantity,
                         'cartid'=>$ct->id
                         ];
                         $saleDiscountValue=$saleDiscountValue+(($product->price*$quantity)-($price));
                         array_push($productDtl, $data);
                 }
              array_push($productId, $productDtl[0]);
            }
            if($voucher){
                $sellerShopData=SellerData::where('id',$uniquesell->id)->first();
                $coupon=Coupon::where('code',$voucher->coupon_code)->where('seller_guid',$sellerShopData->guid)->first();
                $voucherDiscount=$coupon->discount;
            }
            $totalDiscountValue+=$voucherDiscount;
         
                $sellerdata = [
                    "storeid"=> $uniquesell->id,
                    "storename"=> $uniquesell->fullname,
                    'products'=>$productId,
                    'coupon_code'=>$voucher->coupon_code??"",
                    'voucher_discount'=>$voucherDiscount
                ];
                array_push($uniquesellss, $sellerdata);
         }
        
          $productIds=array();
        foreach($userCart as $cart){
            array_push($productIds, $cart->product_id);
        }
        $products = Product::whereIn('id', $productIds)->get();
        
        $shipping = array();
        $weight=array();
        foreach($products as $pro){
            array_push($shipping, $pro->shipping_price);
            array_push($weight,$pro->weight??1);
            
        }
        $shippingTotal = array_sum($shipping);
        $weightTotal=array_sum($weight);
        $total=array();
        foreach($userCart as $cart){
            array_push($total, $cart->price);
        }
        $orderTotal = array_sum($total);
        $totalOrder = $orderTotal;
        $discountValue=$totalDiscountValue;    
        $discount=$totalDiscountValue;
        $finalOrder=$totalOrder-$discountValue;
        $discount+=$saleDiscountValue;
        $discountValue+=$saleDiscountValue;
       if($uniquesellss){
            return response()->json([
                    'success'=> true,
                    'sub_total'=> $totalOrder+$saleDiscountValue,
                    'discount'=>$discount,
                    'total'=>$finalOrder,
                    'shipping'=> $shippingTotal,
                    'weight'=>$weightTotal,
                    'data'=> $uniquesellss,
                ]);  
       }else{
           return response()->json([
                    'success'=> false,
                    'message' => 'Failed to Fetch Data'
                ]);
       }  
        //  $userCart = UserCart::where('user_id', \Auth::user()->id)->get();
        //  $shop=array();
  
        // foreach($userCart as $cart){
        //     // array_push($shopId, $cart->shop_id);
        //   $sellerdata =  SellerData::where('id', $cart->shop_id)->with(['products' => function ($query) {
        //       $cart = UserCart::where('user_id', \Auth::user()->id)->get();
        //       $productId = array();
        //       foreach($cart as $ct){
        //           array_push($productId, $ct->product_id);
        //       }
        //         $query->with(['cart'=> function (HasMany $hasMany) {
                    
        //             $hasMany->select(['product_id', 'quantity'])
        //             ->whereIn('product_id', $productId);
        //         }])
        //         ->whereIn('id', $productId);
        //     }])->first(['id', 'fullname']);
            
        //     array_push($shop, $sellerdata);
        // }
        // return $shop;
        
        
        
        
        
        //  $userCart = UserCart::where('user_id', \Auth::user()->id)
        //  ->with(['shop' => function (BelongsTo $query) {
        //     $query->select(['id', 'fullname'])->with(['products'=> function (HasMany $hasMany) {
        //         $cart = UserCart::where('user_id', \Auth::user()->id)->get();
        //         $productId = array();
        //          foreach($cart as $ct){
        //               array_push($productId, $ct->product_id);
        //           }
        //         // $hasMany->select(['id', 'name']);
        //         $hasMany->whereIn('id', $productId);
        //     }]);
        //     // ->with(['products' => function (BelongsTo $belongsTo) {
        //     // $belongsTo->select(['id', 'name']);
        //     // }]);
        // }])
        //  ->get();
        //  return $userCart;
        //  die();
        // $shop=array();
        // foreach($userCart as $cart){
        //     // array_push($shopId, $cart->shop_id);
        //     $sellerdata =  SellerData::where('id', $cart->shop_id)->with(['products'  => function (HasMany $belongsTo){
        //         $belongsTo->select(['id', 'name'])->withCategory();
        //     }])->get();
        //   $sellerdata =  SellerData::where('id', $cart->shop_id)->with(['products' => function (BelongsTo $belongsTo) {
        //     //   $cart = UserCart::where('user_id', \Auth::user()->id)->get();
        //     //   $productId = array();
        //     //   foreach($cart as $ct){
        //     //       array_push($productId, $ct->product_id);
        //     //   }
        //     //   $belongsTo->select(['id', 'name']);
        //     //     // $query->whereIn('id', $productId);
        //     }])->first();
            
        //     array_push($shop, $sellerdata);
        // }
        // return $shop;
        // return CheckOut::with(['user'])
        // ->with(['cart'])
        // ->get();
        // $userCart = UserCart::where('user_id', \Auth::user()->id)
        // ->with(['products' => function (BelongsTo $belongsTo) {
        //     $belongsTo->select(['id', 'name']);
        // }])
        // ->get();
    //       $userCart = UserCart::where('user_id', \Auth::user()->id)->get();
    //     $shop=array();
    //     $sellerShops = array();
    //     foreach($userCart as $cart){
    //         $seller =  SellerData::where('id', $cart->shop_id)->first();
    //         $product = Product::where('id', $cart->product_id)
    //         ->with('category')
    //         ->with('media')
    //         ->with('user')
    //         ->get();
    //       $sellerdata=[];
    //       array_push($sellerShops, $seller);
    //       $sellerShop = array_unique($sellerShops);
    //       foreach($sellerShop as $sellerSho){
    //             $sellerdata = [
    //                 "id"=> $sellerSho->id,
    //                 "fullname"=> $sellerSho->fullname
    //             ];
            
    //       }
            
    //         // $products = array();
    //         // foreach($product as $pro){
    //         //     if($pro->auctioned == 1){
    //         //         $product =[
    //         //             'id'=>$pro->id,
    //         //             'name'=>$pro->name,
    //         //             'bids'=> $pro->bids,
    //         //             'shop_id'=>$pro->shop_id,
    //         //             'media'=>$pro->media  
    //         //         ];
    //         //     }else if($pro->selling_now == 1){
    //         //         $product =[
    //         //             'id'=>$pro->id,
    //         //             'name'=>$pro->name,
    //         //             'price'=> $pro->price,
    //         //             'shop_id'=>$pro->shop_id,
    //         //             'media'=>$pro->media  
    //         //         ];
    //         //     }
    //         //     array_push($products, $product);
    //         // }
    //         // array_values($products)
    //         // $sellerdata="";
    //         // if($product->selling_now == 1){
    //     //         $sellerdata = array(
    //     //             "id"=> $seller->id,
    //     //             "fullname"=> $seller->fullname,
    //     //             // 'products'=> array_values($products)
    //     //             // 'products'=>array(
    //     //             //         'id'=>$product->id,
    //     //             //         'name'=>$product->name,
    //     //             //         'price'=> $product->price,
    //     //             //         'media'=>$product->media
    //     //             //     )
    //     //             );                
    //     //     // }else if($product->auctioned == 1){
    //     //     //       $sellerdata = array(
    //     //     //         "id"=> $seller->id,
    //     //     //         "fullname"=> $seller->fullname,
    //     //     //         'products'=>array(
    //     //     //                 'id'=>$product->id,
    //     //     //                 'name'=>$product->name,
    //     //     //                 'bids'=> $product->bids,
    //     //     //                 'media'=>$product->media
    //     //     //             )
    //     //     //         );  
    //     //     // }

    //     // //   $sellerdata =  SellerData::where('id', $cart->shop_id)->with(['products' => function ($query) {
    //     // //       $cart = UserCart::where('user_id', \Auth::user()->id)->get();
    //     // //       $productId = array();
    //     // //       foreach($cart as $ct){
    //     // //           array_push($productId, $ct->product_id);
    //     // //       }
    //     // //         $query->whereIn('id', $productId);
    //     // //     }])->first();
            
    //         // array_push($shop, $sellerdata);
    //     }
        
    //     return $shop;
    //     die();
    //     $productId=array();
    //     foreach($userCart as $cart){
    //         array_push($productId, $cart->product_id);
    //     }
    //     $products = Product::whereIn('id', $productId)->get();
        
    //     $shipping = array();
    //     foreach($products as $pro){
    //         array_push($shipping, $pro->shipping_price);
    //     }
    //     $shippingTotal = array_sum($shipping);
        
    //     $total=array();
    //     foreach($userCart as $cart){
    //         array_push($total, $cart->price);
    //     }
    //     $orderTotal = array_sum($total);
    //     $totalOrder = $orderTotal + $shippingTotal;
    //     // return $shop;
        
    //     // $shopData = array_unique($shop);
    //     // $collection = array_values($shopData);
        
    //   if($shop){
    //         return response()->json([
    //                 'success'=> true,
    //                 'total'=> $totalOrder,                    
    //                 'shipping'=> $shippingTotal,
    //                 'data'=> $shop,
    //             ]);  
    //   }else{
    //       return response()->json([
    //                 'success'=> false,
    //                 'message' => 'Failed to Fetch Data'
    //             ]);
    //   }
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
            
            CheckOut::where('user_id', \Auth::user()->id)->delete();
            
            // $cart = UserCart::where('id',$request->get('cart_id'))->first();
            $product= Product::where('id', $request->get('product_id'))->first();
            $checkout = new CheckOut();
            $checkout->guid = GuidHelper::getGuid();
            $checkout->cart_id = "";//$request->get('cart_id');
            $checkout->user_id = \Auth::user()->id;
            $checkout->dicount_code = "";//$request->get('dicount_code');
            $checkout->items_number = "";//$request->get('items_number');
            if($product->auctioned){
                $checkout->sub_total = $product->bids;
            }else if($product->selling_now){
                if($product->sale_price>0 && $product->sale_price!=NULL)
                {
                    $checkout->sub_total = $product->sale_price;
                }
                else{
                    $checkout->sub_total = $product->price;
                }
                
            }
             if($request->get('quantity')>$product->stockcapacity){
                return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
            }
            // $checkout->sub_total = "";//$request->get('sub_total');
            $checkout->quantity = $request->get('quantity');
            $checkout->shipping_total = $product->shipping_price;//$request->get('shipping_total');
            $checkout->admin_prices = "";//json_encode($request->get('admin_prices'));
            // $checkout->order_total = "";//$request->get('order_total');
            if($product->auctioned){
                $checkout->order_total = $product->bids * $request->get('quantity');
            }else if($product->selling_now){
                if($product->sale_price>0 && $product->sale_price!=NULL)
                {
                    $checkout->order_total = $product->sale_price * $request->get('quantity');
                }
                else{
                    $checkout->order_total = $product->price * $request->get('quantity');
                }
           
            }
            $checkout->product_id = $request->get('product_id');//$request->get('order_total');
            $checkout->store_id = $product->shop_id;//$request->get('order_total');
            $checkout->attri=json_encode($request->get('attributes'))??"[]";
            $checkout->save();
            
            if($checkout){
                return response()->json([
                    'success'=> true,
                    'message' => 'Checkout Added!'
                ]);
            }else{
                return response()->json([
                    'success'=> false,
                    'message' => 'Unable to Add Checkout!'
                ]);
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
        $checkout = CheckOut::where('id', $id)->first();
        $checkout->delete();
        return back()->with('success', 'Checkout deleted');

    }
    
}
