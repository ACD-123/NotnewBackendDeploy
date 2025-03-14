<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCart;
use App\Models\Product;
use App\Models\SellerData;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Coupon;
use Carbon\Carbon;


class UserCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserCart::with(['user'])
        ->with(['products'])
        ->with(['shop'])
        ->where('user_id', Auth::user()->id)
        ->get();
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
    protected function validator(array $data)
    {
         return Validator::make($data, [
            'price' => ['required'],
            //'quantity' => ['required', 'integer', 'max:10'],
            // 'user_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            // 'attributes' => ['required'],
        ]);
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
            $validator = $this->validator($request->all());
            $cart = "";
            if (!$validator->fails()) {
                $cartExits = UserCart::where('user_id', Auth::user()->id)
                ->where('product_id',$request->get('product_id'))
                ->where('attributes',json_encode($request->get('attributes')))->first();
                $product=Product::find($request->get('product_id'));
                if($request->get('quantity')>$product->stockcapacity){
                    return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
                }
                if(!empty($cartExits)){
                    $cartQty = $cartExits->quantity;
                    $cartQty =  $cartQty + $request->get('quantity');
                    if($request->get('quantity')>$product->stockcapacity){
                        return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
                    }
                    $productPrice=$product->price;
                    if($product->sale_price>0){
                        $productPrice=$product->sale_price;
                    }
                    $cart = UserCart::where('user_id', Auth::user()->id)
                    ->where('product_id',$request->get('product_id'))
                    ->where('attributes',json_encode($request->get('attributes')))
                    ->update(
                        [
                            'product_id' => $request->get('product_id'),
                            'price' =>  $productPrice*$cartQty,
                            'quantity' =>   $cartQty,
                            'attributes' => json_encode($request->get('attributes')) ? json_encode($request->get('attributes')) : '{}',
                            'user_id' =>  Auth::user()->id,
                            'shop_id' =>  $request->get('shop_id')
                        ]);
                        $product->update([
                            "stockcapacity"=>$product->stockcapacity-$request->get('quantity')
                        ]);
                }else{
                    $productPrice=$product->price;
                    if($product->sale_price>0){
                        $productPrice=$product->sale_price;
                    }
                    $cart = new UserCart();
                    $cart->product_id =  $request->get('product_id');
                    // $cart->name =  $request->get('name');
                    $cart->price =  $productPrice*$request->get('quantity');
                    $cart->quantity =  $request->get('quantity');
                    $cart->attributes = json_encode($request->get('attributes')) ? json_encode($request->get('attributes')) : '{}';
                    $cart->user_id =  Auth::user()->id;//$request->get('user_id');//\Auth::user()->id,;
                    $cart->shop_id =  $request->get('shop_id');
                   //$cart->fill($request->all())->save();
                   $cart->save();
                   $product->update([
                    "stockcapacity"=>$product->stockcapacity - $request->get('quantity')
                ]);
                }

                return response()->json([
                    'success' => true,
                    'cart' => $cart,
                    'message' => "Product has been added to Cart"
                ], 200);
            }
            return response()->json([
                'success' => false,
                'errors' => $this,
                'message' => $validator->getMessageBag()
            ], 401);
        });

    }
    
     public function storeGuest(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $validator = $this->validator($request->all());
            $cart = "";
            if (!$validator->fails()) {
                $cartExits = UserCart::where('guest_user_id', $request->get('user_id'))
                ->where('product_id',$request->get('product_id'))
                ->where('attributes',json_encode($request->get('attributes')))->first();

                $product=Product::find($request->get('product_id'));
                if($request->get('quantity')>$product->stockcapacity){
                    return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
                }
                if(!empty($cartExits)){
                    $cartQty = $cartExits->quantity;
                    $cartQty =  $cartQty + $request->get('quantity');
                    if($request->get('quantity')>$product->stockcapacity){
                        return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
                    }
                    $productPrice=$product->price;
                    if($product->sale_price>0){
                        $productPrice=$product->sale_price;
                    }
                    // $cartQty =  $cartQty + $request->get('quantity');
                    $cart = UserCart::where('guest_user_id', $request->get('user_id'))
                    ->where('product_id',$request->get('product_id'))->update(
                        [
                            'product_id' => $request->get('product_id'),
                            'price' =>   $productPrice*$cartQty,
                            'quantity' =>   $cartQty,//$request->get('quantity'),
                            'attributes' => json_encode($request->get('attributes')) ? json_encode($request->get('attributes')) : '[]',
                            'guest_user_id' =>  $request->get('user_id'),
                            'shop_id' =>  $request->get('shop_id')
                        ]);
                        $product->update([
                            "stockcapacity"=>$product->stockcapacity-$request->get('quantity')
                        ]);
                        
                }else{
                    $productPrice=$product->price;
                    if($product->sale_price>0){
                        $productPrice=$product->sale_price;
                    }
                    $cart = new UserCart();
                    $cart->product_id =  $request->get('product_id');
                    // $cart->name =  $request->get('name');
                    $cart->price =  $productPrice*$request->get('quantity');
                    $cart->quantity =  $request->get('quantity');
                    $cart->attributes = json_encode($request->get('attributes')) ? json_encode($request->get('attributes')) : '[]';
                    $cart->guest_user_id =  $request->get('user_id');//$request->get('user_id');//\Auth::user()->id,;
                    $cart->shop_id =  $request->get('shop_id');
                   //$cart->fill($request->all())->save();
                   $cart->save();
                   $product->update([
                    "stockcapacity"=>$product->stockcapacity - $request->get('quantity')
                ]);
                }

                return response()->json([
                    'success' => true,
                    'cart' => $cart,
                    'message' => "Product has been added to Cart"
                ], 200);
            }
            return response()->json([
                'success' => false,
                'errors' => $this,
                'message' => $validator->getMessageBag()
            ], 401);
        });

    }
    
      public function reorder(Request $request)
    {
        $data = $request->all();
       $test=json_decode($data['data'],true);
        try{
            $values=$test;
            foreach ($values as  $value) {
                $cartExits = UserCart::where('user_id', Auth::user()->id)
                ->where('product_id',$value['product_id'])->first();
                $product=Product::find($value['product_id']);
                if($value['quantity']<$product->stockcapacity){
                    if(!empty($cartExits)){
                        $cartQty = $cartExits->quantity;
                        $cartQty =  $cartQty + $value['quantity'];
                        
                        $cart = UserCart::where('user_id', Auth::user()->id)
                        ->where('product_id',$value['product_id'])->update(
                            [
                                'product_id' =>$value['product_id'],
                                'price' =>   $product->price,
                                'quantity' =>   $cartQty,
                                'attributes' => json_encode($value['attributes'],true),
                                'user_id' =>  Auth::user()->id,
                                'shop_id' =>  $product->shop_id
                            ]);
                            $product->update([
                                "stockcapacity"=>$product->stockcapacity-$value['quantity']
                            ]);
                    }else{
                        $cart = new UserCart();
                        $cart->product_id =  $value['product_id'];
                        $cart->price =  $product->id;
                        $cart->quantity =  $value['quantity'];
                        $cart->attributes = json_encode($value['attributes'],true);
                        $cart->user_id =  Auth::user()->id;//$request->get('user_id');//\Auth::user()->id,;
                        $cart->shop_id =  $product->shop_id;
                       $cart->save();
                       $product->update([
                        "stockcapacity"=>$product->stockcapacity - $value['quantity']
                    ]);
                    }
                }
            }
            $cart=UserCart::where('user_id', Auth::user()->id)->get();
            return response()->json(['status' => true, 'message' => "Items Added to Cart Successfully",'data'=>$cart], 200);
        }
        catch(\Exception $e)
        {
            return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]], 400);
        }
    }
    
      public function reorderWeb(Request $request)
    {
        $data = $request->all();
       $test=json_decode($data['data'],true);
        try{
            $value=$test[0];
            
                $cartExits = UserCart::where('user_id', Auth::user()->id)
                ->where('product_id',$value['product_id'])->first();
                $product=Product::find($value['product_id']);
                if($value['quantity']<$product->stockcapacity){
                    if(!empty($cartExits)){
                        $cartQty = $cartExits->quantity;
                        $cartQty =  $cartQty + $value['quantity'];
                        
                        $cart = UserCart::where('user_id', Auth::user()->id)
                        ->where('product_id',$value['product_id'])->update(
                            [
                                'product_id' =>$value['product_id'],
                                'price' =>   $product->price,
                                'quantity' =>   $cartQty,
                                'attributes' => json_encode($value['attributes'],true),
                                'user_id' =>  Auth::user()->id,
                                'shop_id' =>  $product->shop_id
                            ]);
                            $product->update([
                                "stockcapacity"=>$product->stockcapacity-$value['quantity']
                            ]);
                    }else{
                        $cart = new UserCart();
                        $cart->product_id =  $value['product_id'];
                        $cart->price =  $product->id;
                        $cart->quantity =  $value['quantity'];
                        $cart->attributes = json_encode($value['attributes'],true);
                        $cart->user_id =  Auth::user()->id;//$request->get('user_id');//\Auth::user()->id,;
                        $cart->shop_id =  $product->shop_id;
                       $cart->save();
                       $product->update([
                        "stockcapacity"=>$product->stockcapacity - $value['quantity']
                    ]);
                    }
                }
                
            
            $cart=UserCart::where('user_id', Auth::user()->id)->get();
            if($value['quantity']<$product->stockcapacity){
                return response()->json(['status' => true, 'message' => "Items Added to Cart Successfully",'data'=>$cart], 200);
                
            }
            else{
                return response()->json(['status' => true, 'message' => "Product Out of Stock!",'data'=>$cart], 400);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cart = UserCart::where('id', Auth::user()->id)->first();
        return $cart;

    }
    
        public function selfGuest(Request $request)
    {
        $userCart = UserCart::where('guest_user_id', $request->user_id)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
            // ->where('ordered', false)->get();
            // $products = [];
            // foreach($cart as $c){
            //     $products['height'] = '10';//$c->products->height;
            //     $products['width'] = '10';//$c->products->width;
            //     $products['length'] = '10';//$c->products->length;
            //     $products['actual_weight'] ='10';// $c->products->weight;
            //     // array_push($products, $c->products);
            // }
        //  if($cart){
        //         return response()->json(['status'=> true,'data' =>$cart], 200);       
        //     }else{
        //         return response()->json(['status'=> false,'data' =>"Unable to Get Cart"], 400);       
        //     } 
        $couponDiscount=0;
        $couponCode="";
        $shopId=array();
        
        foreach($userCart as $cart){
            array_push($shopId, $cart->shop_id);
           $cart->attributes= json_decode(json_decode($cart->attributes,true));
        }
        $shops = SellerData::whereIn('id', $shopId)
            ->with(['products'])
            ->get();
        $productId=array();
        foreach($userCart as $cart){
            array_push($productId, $cart->product_id);
        }
        $products = Product::whereIn('id', $productId)->get();
        
        $shipping = array();
        foreach($products as $pro){
            array_push($shipping, $pro->shipping_price);
        }
        $shippingTotal = array_sum($shipping);

        $quntity = array();
        foreach($userCart as $cart){
            array_push($quntity, $cart->quantity);
        }
        $quntityTotal = array_sum($quntity);
        
        $total=array();
        foreach($userCart as $cart){
            
            array_push($total, $cart->price);
        }
        $orderTotal = array_sum($total);
        $totalOrder = $orderTotal + 0;
        if(count($userCart)>0)
        {
            $cartValue=$userCart[0]->coupon_code;
            if(!empty($cartValue)){
                $coupon=Coupon::where('code',$cartValue)->first();
                if($coupon){
                    $couponCode=$cartValue;
                    $couponDiscount=$coupon->discount;
                }
            }
        }
        if($userCart){
            return response()->json([
                    'success'=> true,
                    'coupon' => $couponCode,
                    'coupon_discount'=>$couponDiscount,
                    'subtotal'=>$orderTotal,
                    'product_count'=>$quntityTotal,
                    'total'=> $totalOrder, 
                    'sub_total'=>$orderTotal,
                    'shipping'=> 0,
                    'data'=> $userCart,
                ]);  
       }else{
           return response()->json([
                    'success'=> false,
                    'message' => 'Unable to Get Cart'
                ]);
       }
    }

        public function self()
    {
        $user = Auth::user();
        $userCart = UserCart::where('user_id', $user->id)
            ->with(['products'])
            ->with(['user'])
            ->with(['shop'])
            ->with(['savelater'])
            ->get();
     
        $couponDiscount=0;
        $couponCode="";
        $shopId=array();
        
        foreach($userCart as $cart){
            array_push($shopId, $cart->shop_id);
           $cart->attributes= json_decode(json_decode($cart->attributes,true));
        }
        $shops = SellerData::whereIn('id', $shopId)
            ->with(['products'])
            ->get();
        $productId=array();
        foreach($userCart as $cart){
            array_push($productId, $cart->product_id);
        }
        $products = Product::whereIn('id', $productId)->get();
        
        $shipping = array();
        foreach($products as $pro){
            array_push($shipping, $pro->shipping_price);
        }
        $shippingTotal = array_sum($shipping);

        $quntity = array();
        foreach($userCart as $cart){
            array_push($quntity, $cart->quantity);
        }
        $quntityTotal = array_sum($quntity);
        
        $total=array();
        foreach($userCart as $cart){
            array_push($total, $cart->price);
        }
        $orderTotal = array_sum($total);
        $totalOrder = $orderTotal + 0;
        if($userCart){
            return response()->json([
                    'success'=> true,
                    'coupon' => $couponCode,
                    'coupon_discount'=>$couponDiscount,
                    'subtotal'=>$orderTotal,
                    'product_count'=>$quntityTotal,
                    'total'=> $totalOrder, 
                    'sub_total'=>$orderTotal,
                    'shipping'=> 0,
                    'data'=> $userCart,
                ]);  
       }else{
           return response()->json([
                    'success'=> false,
                    'message' => 'Unable to Get Cart'
                ]);
       }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $userCart =UserCart::where('id', $id)->first();
        // return $id;
        // $userCart = UserCart::where('id', $id)
        // ->update($request->all());
        //     return response()->json([
        //     'success' => true,
        //     'cart' => $userCart,
        //     'message' => "Cart Updated"
        //     ], 200);

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
         $userCart =UserCart::where('id', $id)->first();
         $product = Product::where('id', $userCart->product_id)->first();
         $quantity = $userCart->quantity + $request->get('quantity');
         if($request->get('quantity')>$product->stockcapacity){
            return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
        }
         $price = "";
         if($product->auctioned){
            $price = $product->bids * $request->get('quantity');     
         }else if($product->selling_now){
            if($product->sale_price != NULL && $product->sale_price > 0){
                $price = $product->sale_price * $request->get('quantity');
            }
            else{
                $price = $product->price * $request->get('quantity');
            }
           
         }
         $update = UserCart::where('id', $id)->update([
             "quantity"=>$request->get('quantity'),
             "price"=>$price,
         ]);
         return UserCart::where('id', $id)->first();
         
    }
    
    public function guestUpdate(Request $request, $id)
    {
         $userCart =UserCart::where('id', $id)->first();
         $product = Product::where('id', $userCart->product_id)->first();
         $quantity = $userCart->quantity + $request->get('quantity');
         if($request->get('quantity')>$product->stockcapacity){
            return response()->json(['status'=> false,'data'=>[],'message' =>"Total Remaining Stock is ".$product->stockcapacity], 400); 
        }
         $price = "";
         if($product->auctioned){
            $price = $product->bids * $request->get('quantity');     
         }else if($product->selling_now){
            $price = $product->price * $request->get('quantity');
         }
         $update = UserCart::where('id', $id)->update([
             "quantity"=>$request->get('quantity'),
             "price"=>$price,
         ]);
         return UserCart::where('id', $id)->first();
         
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $userCart = UserCart::where('id', $request->get('cart_id'))->first();
        $product = Product::find($userCart->product_id);
        $product->update([
            "stockcapacity"=>$product->stockcapacity+$userCart->quantity
        ]);
        $userCart->delete();
        return response()->json([
            'success' => true,
            'message' => "Item Deleted"
        ], 200);
    }
    public function clear()
    {
        $carts=UserCart::where('user_id', Auth::user()->id)->where('is_auctioned',0)->get();
        foreach($carts as $cart){
            $product = Product::find($cart->product_id);
            $product->update([
                "stockcapacity"=>$product->stockcapacity+$cart->quantity
            ]);
        }
        UserCart::where('user_id', Auth::user()->id)->where('is_auctioned',0)->delete();
        return response()->json([
            'success' => true,
            'message' => "Cart has been Clear"
        ], 200);
       
    }
    public function guestDestroy(Request $request)
    {
        $userCart = UserCart::where('id', $request->get('cart_id'))->first();
        $product = Product::find($userCart->product_id);
        $product->update([
            "stockcapacity"=>$product->stockcapacity+$userCart->quantity
        ]);
        $userCart->delete();
        return response()->json([
            'success' => true,
            'message' => "Item Deleted"
        ], 200);
    }
    public function guestClear(Request $request)
    {
        $carts=UserCart::where('guest_user_id', $request->user_id)->where('is_auctioned',0)->get();
        foreach($carts as $cart){
            $product = Product::find($cart->product_id);
            $product->update([
                "stockcapacity"=>$product->stockcapacity+$cart->quantity
            ]);
        }
        UserCart::where('guest_user_id', $request->user_id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Cart has been Clear"
        ], 200);
       
    }
    /**
     * Count total Coupns of User.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function count()
    {
       return UserCart::where('user_id', Auth::user()->id)->count();
    }
    public function guestCount(Request $request)
    {
       return UserCart::where('guest_user_id',$request->user_id)->count();
    }
    public function deleteCartAll()
    {
        try {
           $carts=UserCart::where('created_at', '<', Carbon::today())->get();
           foreach ($carts as $value) {
            $value->delete();
           }
           return customApiResponse(true, [], 'Cart Deleted!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong!', 500);
        }
    }
}
