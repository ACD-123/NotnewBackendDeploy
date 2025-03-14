<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GuidHelper;
use App\Helpers\ArrayHelper;
use App\Helpers\StripeHelper;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\UserOrderSummary;
use AP\Models\Transaction;
use App\Models\UserNotification;
use App\Models\Fedex;
use App\Models\OutStock;
use App\Models\SaveAddress;
use App\Models\EasyPost;
use App\Models\SellerData;
use App\Models\UserOrderDetails;
use App\Models\USPS;
use App\Models\Stock;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\UserCart;
use App\Models\UserOrder;
use App\Models\Prices;
use App\Models\Product;
use App\Models\flexefee;
use App\Models\FeedBack;
use App\Models\ProductShippingDetail;
use App\Models\Refund;
use App\Models\ShippingDetail;
use App\Models\User;
use App\Models\ShippingSize;
use App\Models\PaymentsLog;
use App\Models\PaymentsVendorLog;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderPlacedSeller;
use App\Models\TrustedSeller;
use App\Notifications\DepositAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stripe\StripeClient;
use Carbon\Carbon;
use App\Jobs\captureFunds;
use Illuminate\Support\Facades\Artisan;
use App\Traits\AppliedFees;
use App\Models\SellerTransaction;
use App\Services\FCMService;
use App\Models\UserStoreVouchers;
use App\Models\WalletTransaction;
use App\Models\CheckOut;


class OrderController extends Controller
{


    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    const SERVICETYPE = 'FEDEX_GROUND';//'STANDARD_OVERNIGHT';
    const FEDEXTESTSENTTRACKING = '111111111111';
    const FEDEXTESTDELIVEREDTRACKING = '122816215025810';
    const INCOMPLETE_STATUS='Incomplete',
     COMPLETE_STATUS='Succeeded',
     FEATURED_ADD = 'Featured Add',
     HIRE_CAPTAIN = 'Hire Captain',
     BUYING="Buying";
    use AppliedFees; 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $data['order'] = Order::orWhere(
            'buyer_id',
            Auth::user()->id
        )->orWhere('seller_id', Auth::user()->id)
            ->with(["product" => function (BelongsTo $hasMany) {
                $hasMany->select(Product::defaultSelect());
            }, "buyer" => function (BelongsTo $hasMany) {
                $hasMany->select(User::defaultSelect());
            }, 'shippingDetail' => function (BelongsTo $hasMany) {
                $hasMany->select(ShippingDetail::defaultSelect());
            }, 'refund' => function ($query) {
                $query->select(Refund::defaultSelect());
            }])->get();

        return $data['order'];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Update for USPS
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update_USPS(Order $order, Request $request){
        try{
            return DB::transaction(function () use ($request, $order) { 
    

                $shouldUpdate = true;
                 
                if ($request->has('status')) {
                    
                    $stripe = new StripeClient(env('STRIPE_SK'));
                    $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));
                  
                    if ($paymentIntent->id !== $request->get('payment_intent') || $paymentIntent->status !== 'requires_capture'){
                         
                        $shouldUpdate = false;
                    }
                        
                }
    
                // if ($shouldUpdate) {
                    $buyer = User::where('id', $order->buyer_id)->first();
                    $seller = User::where('id', $order->seller_id)->first();
                    $product = Product::where('id', $order->product_id)->first();
                    $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();
                   
                    $resp = '<CarrierPickupScheduleRequest USERID="974FLEXM7409">
                                <FirstName>'. $buyer->name .'</FirstName>
                                <LastName>'. $buyer->name .'</LastName>
                                <FirmName>NotNew</FirmName>
                                <SuiteOrApt>Suite 101</SuiteOrApt>
                                <Address2>'. $buyer_shipping->street_address .'</Address2>
                                <Urbanization></Urbanization>
                                <City>'. $buyer_shipping->city .'</City>
                                <State>'.  $buyer_shipping->state .'</State>
                                <ZIP5>'. $buyer_shipping->zip .'</ZIP5>
                                <ZIP4>1000</ZIP4>
                                <Phone>5555551234</Phone>
                                <Extension></Extension>
                                <Package>
                                <ServiceType>PriorityMailExpress</ServiceType>
                                <Count>2</Count>
                                </Package>
                                <Package>
                                <ServiceType>PriorityMail</ServiceType>
                                <Count>1</Count>
                                </Package>
                                <EstimatedWeight>14</EstimatedWeight>   
                                <PackageLocation>Front Door</PackageLocation>
                                <SpecialInstructions>Packages are behind the screen door.</SpecialInstructions>
                            </CarrierPickupScheduleRequest>';
                    
                    $_shipment = USPS::createShipment($resp);
                    
                    $req = $request->all();
                    if (isset($_shipment["errors"])) {
                        
                        throw new \Exception($_shipment["errors"][0]['message'], 1);
                        
                    } else if (isset($_shipment["ConfirmationNumber"])) {

                        $metadata = null;
                        $req["tracking_id"] = $_shipment["ConfirmationNumber"];
                        $req["fedex_shipping"] = json_encode($_shipment);
                        $order->fill($req);
                        $order->update();
                        $paymentmode = "Incomplete";
                        $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                        $product->is_sold = true;
                        $product->update();
                        // Artisan::call('queue:work  --daemon');
                        // @Todo: create a different controller action for order confirmation
                        if ($request->has('status')) {
                            /** @var User $user */
                            $user = Auth::user();
                            $user->notify(new OrderPlaced($order));
                            $seller->notify(new OrderPlacedSeller($order));
                        }
                        // depositStripeFund::dispatch();
                        // Artisan::call('schedule:run');
            
                    }
                // }
    
            });
        }
        catch(Exception $e) {
            throw $e;
        }finally{
            $stripe = new StripeClient(env('STRIPE_SK'));
            $seller = User::where('id', $order->seller_id)->first();
            $account = $stripe->accounts->retrieve(
                $seller->stripe_account_id,
                []
              );
              if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
              {
                $user = Auth::user();
                $user->notify(new DepositAccount($order));
              }else{
                //Artisan::call('capture:funds');
                return $order;
              }
       }
    }   
    public function store__(Request $request){
        return "store_";
        die();
        $request="";
        $order="";
        return DB::transaction(function () use ($request, $order) {  
            $stripe = new StripeClient(env('STRIPE_SK'));
            //for Updating Order Status Delivered by EasyPost
            $orders_delivered = Order::whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
               ->where('status', Order::STATUS_UNCAPTURED)
            //    ->where('id','231')
                ->whereNotNull('payment_intent')
                ->each(function (Order $order) use ($stripe) {
                    $data = "";
                    $trackingId = $order->tracking_id;
                    $trackerShipment = EasyPost::trackShipment($data);
                    $trackerShipment = json_decode($trackerShipment);
                    if($trackerShipment->status == Order::DELIVERED){
                        $order->deliver_status = Order::DELIVERED;
                        $order->update();
                    };      
                });
                
            //For Payments
            $orders = Order::whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
               ->where('status', Order::STATUS_UNCAPTURED)
               ->where('deliver_status',Order::DELIVERED)
                ->whereNotNull('payment_intent')
                ->each(function (Order $order) use ($stripe) {
                    $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent);
                    if($paymentIntent->status === 'requires_capture')
                    {
                        $trustedSeller = TrustedSeller::where('user_id',$order->seller_id)->first();
                        if($trustedSeller){
                            $stripe->paymentIntents->capture($order->payment_intent);
                        }else{
                            
                            $stripe->paymentIntents->capture($orders->payment_intent);
                            //$value = \Session::get('transferGroup');
                            // $charge = $stripe->charges->create(array(
                            //     'currency' => 'USD',
                            //     'amount'   => $paymentIntent->amount,
                            //     'source' => 'tok_bypassPending'
                            // ));
                            $value = $orders->price;//$paymentIntent['amount_received'];
                           
                            //Remaining after subtracting Stripe Fee => Total Amount
                            $totalAmount = $this->feePriceCalculator($orders->price);
                            
                            // $remaining = $value - $feePriceCalculator;
                            $flexePoint = 10;
                            $flexeAmount = ($flexePoint/100) * $totalAmount;
                            $sellerAmount = $totalAmount - $flexeAmount;
                            //Getting Seller Account
                            $seller = User::where('id',$orders->seller_id)->first();
                            $product = Product::where('id', $orders->product_id)->first();
                            // Create a Transfer to a connected account (later):
                            $transfer = $stripe->transfers->create([
                                // 'amount' => (int)$remaining,//$product->getPrice() * 100,
                                'amount' => (int)$sellerAmount * 100,//$product->getPrice() * 100,
                                'currency' => 'usd',
                                'destination' => $seller->stripe_account_id,
                                'transfer_group' => $orders->price
                            ]);
                            
                            // // // Create a second Transfer to another connected account (later):
                           
                            $transfer = $stripe->transfers->create([
                                // 'amount' => (int)$percent,
                                'amount' => (int)$flexeAmount * 100,
                                'currency' => 'usd',
                                // Flexe Admin Stripe Account => acct_1MH75gReJCB3JLnc
                                'destination' => 'acct_1MH75gReJCB3JLnc', 
                                'transfer_group' => $orders->price
                            ]);
                            $metadata = null;
                            //For Payment Log
                            PaymentsLog::request($paymentIntent,Order::STATUS_PAID,'Paid To Stripe',$metadata);
                            $order->status = Order::STATUS_PAID;
                            $order->update();
                           
                        }
                }else{
                        
                    /**                         * 
                     * if Captures in stripe but not Updated 
                     * in Orders so it would be PAID 
                     */
                    $metadata = null;
                    $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent);
                    PaymentsLog::request($paymentIntent,Order::STATUS_PAID,'Paid To Stripe',$metadata);
                    $order->status = Order::STATUS_PAID;
                    $order->update();
                }
            });
        });
    }
    public function getUserCompleted(Request $request){
        return UserOrder::where('status',UserOrder::COMPLETED)->get();
    }
    public function getUserCount_(Request $request){
        
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        $buyercompleted = DB::select("SELECT count(`tbl_user_order`.id) as count FROM `tbl_user_order` where buyer_id =".Auth::user()->id." and status ='COMPLETED';");
        $sellercompleted= DB::select("SELECT DISTINCT(`tbl_user_order`.id)FROM `tbl_user_order` inner join tbl_user_order_details on `tbl_user_order`.id = tbl_user_order_details.order_id where tbl_user_order_details.store_id =".$seller->id." and status = 'COMPLETED';");
        $totalCompleted = count($sellercompleted) + $buyercompleted[0]->count;
        
        $selleractive = DB::select("SELECT DISTINCT(`tbl_user_order`.id)FROM `tbl_user_order` inner join tbl_user_order_details on `tbl_user_order`.id = tbl_user_order_details.order_id where tbl_user_order_details.store_id =".$seller->id." and status = 'pending';");
        
        $buyeractive = DB::select("SELECT count(`tbl_user_order`.id) as count FROM `tbl_user_order` where buyer_id =".Auth::user()->id." and status ='pending';");
       
        $totalactive = count($selleractive)+$buyeractive[0]->count;
    //   $seller = SellerData::where('user_id', Auth::user()->id)
    //     ->first();
    //     $buyercompleted = "SELECT count(`tbl_user_order`.id) FROM `tbl_user_order` where buyer_id =".Auth::user()->id." and status ='COMPLETED';";
    //     $sellercompleted="";
        // $sellercompleted = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
        //     ->where('tbl_user_order.status', 'COMPLETED')
        //     ->where('store_id', $seller->id)->count();
        // $buyercompleted = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
        //     ->where('tbl_user_order.status', 'COMPLETED')
        //     ->where('buyer_id', Auth::user()->id)->count();
        // $totalCompleted = $sellercompleted + $buyercompleted;
        
        // $selleractive = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
        // ->where('tbl_user_order.status', 'pending')
        // ->where('store_id', $seller->id)->count();
        
        // $buyeractive = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
        // ->where('tbl_user_order.status', 'pending')
        // ->where('buyer_id', Auth::user()->id)->count();
        // $totalactive = $selleractive+$buyeractive;
        $data =[
                'active'=> $totalactive,
                'completed'=> $totalCompleted
                ];
            return response()->json(['status'=> true,'data' => $data], 200); 
        
    }
    public function getUserCount(Request $request){
       $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller){
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);
        }
        $orderCounts = UserOrder::join('tbl_user_order_details', 'tbl_user_order.id', '=', 'tbl_user_order_details.order_id')
        ->selectRaw("
            COUNT(DISTINCT CASE WHEN tbl_user_order_details.status = 'COMPLETED' AND tbl_user_order_details.refunded = 0 THEN tbl_user_order.id END) as completed,
            COUNT(DISTINCT CASE WHEN tbl_user_order_details.status = 'pending' THEN tbl_user_order.id END) as pending,
            COUNT(DISTINCT CASE WHEN tbl_user_order_details.status = 'accepted' THEN tbl_user_order.id END) as active,
            COUNT(DISTINCT CASE WHEN tbl_user_order_details.status = 'rejected' THEN tbl_user_order.id END) as rejected,
            COUNT(CASE WHEN tbl_user_order_details.status = 'COMPLETED' AND tbl_user_order_details.refunded = 1 THEN tbl_user_order_details.id END) as refunded
        ")
        ->where('tbl_user_order_details.store_id', $seller->id)
        ->first();
    
    $completed = $orderCounts->completed;
    $pending = $orderCounts->pending;
    $active = $orderCounts->active;
    $rejected = $orderCounts->rejected;
    $refunded = $orderCounts->refunded;
    
      
        //  $completed = DB::table('tbl_user_order')
            // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, CONCAT(users.name,"-",users.last_name) as "SellerName"' )
            // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, seller_datas.fullname as "SellerName"' )
            // ->selectRaw('count(*) as completed' )
            // ->join('tbl_user_order_details', 'tbl_user_order_details.order_id', 'tbl_user_order.id')
            // // ->join('users','tbl_user_order.seller_id','=','users.id')
            // // ->join('seller_datas','tbl_user_order.seller_id','=','seller_datas.user_id')
            // ->where('store_id', $seller->id)
            // ->where('tbl_user_order.status', 'COMPLETED')
            // // ->groupBy('id')
        //     // ->first();
        //   $active = DB::table('tbl_user_order')
        //     // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, CONCAT(users.name,"-",users.last_name) as "SellerName"' )
        //     // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, seller_datas.fullname as "SellerName"' )
        //     ->selectRaw('count(*) as active' )
        //     ->join('tbl_user_order_details', 'tbl_user_order_details.order_id', 'tbl_user_order.id')
        //     // ->join('users','tbl_user_order.seller_id','=','users.id')
        //     // ->join('seller_datas','tbl_user_order.seller_id','=','seller_datas.user_id')
        //     ->where('store_id', $seller->id)
        //     ->where('tbl_user_order.status', 'pending')
        //     // ->groupBy('id')
        //     ->first();
        // // $active = UserOrder::where('buyer_id', \Auth::user()->id)->where('status', UserOrder::STATUS_PENDING)->count();
        // // $completed = UserOrder::where('buyer_id', \Auth::user()->id)
        //     ->where('status',UserOrder::COMPLETED)
        //     ->count();
        $data =[
                'active'=> $active,
                'completed'=> $completed,
                'pending'=>$pending,
                'rejected'=>$rejected,
                'refunded'=>$refunded,
                ];
            return response()->json(['status'=> true,'data' => $data], 200); 
        // if($completed && $active){
        //     $data =[
        //         'active'=> $active,
        //         'completed'=> $completed
        //         ];
        //     return response()->json(['status'=> true,'data' => $data], 200);       
        // }else{
        //     return response()->json(['status'=> false,'message' => 0], 400);        
        // }
        // ->count();
    }
    public function getOrderSummary(Request $request){
        return UserOrderSummary::with(['buyer'])
        ->with(['product'])
        ->with(['order'])
        ->where('seller_id', \Auth::user()->id)
        ->get();
    }
    public function getSingleOrderSummary(Request $request, $id){
        return UserOrderSummary::with(['buyer'])
        ->with(['product'])
        ->with(['order'])
        ->where('seller_id', \Auth::user()->id)
        ->where('order_id', $id)
        ->first();
    }
    public function store_stock(Request $request)
    {
        $orderItems = json_decode($request->get("orderItems"));
        $stockData = array();
        foreach($orderItems as $orderItem){
            $stock = Stock::where('product_id', $orderItem->product_id)
            ->where('user_id', Auth::user()->id)->first();
            $quantity= 0;
            if($stock){
                $quantity = $stock->quantity - $orderItem->quantity;//array_push($stockData, $stock->quantity);
                Stock::where('product_id', $orderItem->product_id)->update(['quantity' =>  $quantity]);
            }
        }
        return $stockData;
    }
    public function store(Request $request)
    {     
        $user = User::where('id', Auth::user()->id)->first();
        if($user->address == NULL || $user->address ==""){
            return response()->json(['status'=> false,'message' =>"Please provide address in Profile Section!"], 400); 
        }
        return DB::transaction(function () use ($request) {
        
           
            


            $user = User::where('id', Auth::user()->id)->first();
            $shipping = new ShippingDetail();
            if($request->get("other_address") == true){
                $shipping->user_id = Auth::user()->id;
                $shipping->name = $user->name;
                $shipping->street_address = $request->get("secondaddress");
                $shipping->state = $user->state_id;
                $shipping->city = $user->city_id;
                $shipping->latitude= $user->latitude;
                $shipping->longitude= $user->longitude;
                $shipping->zip = $request->get("zip");
                $shipping->save();
            }
            else{
                
                ShippingDetail::where('user_id',Auth::user()->id)->update([
                                    "user_id" => Auth::user()->id,
                                    "name" => $user->name,
                                    "street_address" => $user->address,
                                    "state" => $user->state_id,
                                    "city" => $user->city_id,
                                    "latitude"=> $user->latitute,
                                    "longitude"=> $user->longitude,
                                    "zip" => $user->zip
                    ]);
               
            }
            $shippingDetails = ShippingDetail::where('user_id',Auth::user()->id)
                ->orderBy('id', 'desc')
                ->first(); 
            
               
            $order = new UserOrder();
            $order->orderid = GuidHelper::getShortGuid();
            $order->buyer_id = Auth::user()->id;
            // $order->seller_id = 1;
            $order->payment_type = $request->get("payment_type");
            $order->billing_address = $shippingDetails->street_address ? $shippingDetails->street_address : $user->address;
            $order->fullname = $user->name;
            $order->phone = $user->phone;
            if($shippingDetails->street_address){
                $order->address = $shippingDetails->street_address ? $shippingDetails->street_address: $request->get("secondaddress");
            }else{
                $order->address = $user->address ? $user->address: $request->get("secondaddress");
                }
            $order->discountcode = $request->get("discountcode");
            // $order->orderItems = json_encode($request->get("orderItems"));
            $order->subtotal_cost = $request->get("subtotal_cost") ? $request->get("subtotal_cost") : 0;
            // $order->actual_cost = $request->get("actual_cost") ? $request->get("actual_cost") : 0;
            $order->shipping_cost = $request->get("shipping_cost") ? $request->get("shipping_cost") : 0;
            // $order->prices = json_encode($request->get("prices"));
            $order->order_total = $request->get("order_total");
            $order->status= UserOrder::STATUS_PENDING;
            $order->payment_intents = $request->get("payment_intents");
            $order->Curency = $request->get("Currency");
            $order->order_type = $request->get("order_type");
            $order->shipping_detail_id = $shippingDetails->id;
            $order->latitute = $user->latitude;
            $order->longitude = $user->longitude;
            $order->zip = $shippingDetails->zip;
            $order->country = $shippingDetails->country;
            $order->state = $shippingDetails->state;
            $order->city = $shippingDetails->city;
            $order->admin_notes=$request->get('note');
            // $order->client_secret = $request->get("payment_intents");
            $order->created_at = Carbon::now()->toDateTimeString();//'2023-01-30 17:40:31';
            $order->updated_at = Carbon::now()->toDateTimeString();//'2023-01-30 17:40:31';
            $order->save();
            if($order){
                
                $arr=array(
                "title"=>"Your Order has been Placed!",
                "message"=>"Your Order has been Placed! Your Order # is ".$order->orderid,
                "user_id"=>Auth::user()->id,
                "type"=>"buying",
                "sender_id"=>Auth::user()->id,
                "notification_type"=>"buying",
                "recieved_from"=>"",
                "product_guid"=>"",
                "room_id"=>"",
                "win"=>0
                );
                StripeHelper::saveNotification($arr);
                $orderItems = json_decode($request->get("orderItems"));
                $sellerIdsNotification=[];
                $totalOrderAmount=0;
                $userCart = UserCart::where('user_id', '=' , Auth::user()->id)->get();
                if($order->order_type=="BuyNow")
                {
                    $userCart=CheckOut::where('user_id', '=' , Auth::user()->id)->get();
                    foreach ($userCart as $value) {
                        $value->price=$value->order_total;
                    }
                }
                $i=0;
                foreach($orderItems as $orderItem){

                    $product = Product::where('id',$orderItem->product_id)->first();
                    $sellerData=SellerData::where('user_id',$product->user_id)->first();
                    UserStoreVouchers::where('user_id',Auth::user()->id)->where('store_id',$sellerData->id)->whereNull('order_id')->update(["order_id"=>$order->id]);
                    $orderdetails = new UserOrderDetails();
                    $orderdetails->order_id = $order->id;
                    $orderdetails->guid = GuidHelper::getGuid();
                    $orderdetails->product_id = $orderItem->product_id;
                    $orderdetails->price = $userCart[$i]->price;
                    $orderdetails->store_id = $product->shop_id;
                    $orderdetails->quantity = $orderItem->quantity;
                    $orderdetails->attributes = json_encode($orderItem->attributes);
                    $orderdetails->save();
                    array_push($sellerIdsNotification,$product->user_id);
                    SellerTransaction::create([
                        "order_id"=>$order->id,
                        "amount"=>$userCart[$i]->price,
                        "type"=>"Order",
                        "message"=>"You Earned $".$userCart[$i]->price." On ".$order->orderid,
                        "seller_guid"=>$sellerData->guid
                    ]);
                   $totalOrderAmount+=$userCart[$i]->price*$orderItem->quantity;
                   $i++;
                }
                $totalOrderAmount+=$order->shipping_cost;
                $sellerIdsNotification=array_unique($sellerIdsNotification);
                foreach($sellerIdsNotification as $s){
                    $arr=array(
                "title"=>"You have recieved a new Order",
                "message"=>"You have recieved a new Order ".$order->orderid." From ".Auth::user()->name,
                "user_id"=>$s,
                "type"=>"selling",
                "sender_id"=>Auth::user()->id,
                "notification_type"=>"selling",
                "recieved_from"=>"",
                "product_guid"=>"",
                "room_id"=>"",
                "win"=>0
                );
                StripeHelper::saveNotification($arr);
                }
           
            }
        

            if($request->get('payment_type') == 'Stripe'){
                $this->stripe = new StripeClient('sk_test_51McZZOBL2ne1CK3D89BPN3QmKiF2hMTZI1IvcdkgZ5asDQrOghL2IC3RnqAAsQK2ctgezVbCUdiwEfu9rv93Visf00eHdE1vlk');       
                $paymentIntent = $this->stripe->paymentIntents->create([
                    // 'amount' => $product->getPrice() * 100,
                    'amount' => $order->actual_cost * 100,
                    'currency' => 'usd',
                    // 'capture_method' => 'manual',
                    // 'transfer_group' => $order->Amount,
                    // 'transfer_data' => [
                    //     // 'amount' => $remaining,
                    //     'destination' => $product->user->stripe_account_id,
                    // ],
                ]);
    
                $payment_intents = $this->stripe->paymentIntents->retrieve(
                    $paymentIntent->id,
                    []
                  );
                  $order->update(
                    [
                        'payment_intents' => $payment_intents->id
                    ]
                );
                if($paymentIntent->status === 'requires_capture')
                {
                  $paymentIntent->capture($order->payment_intents);
                }
                $metadata = null;
                $paymentslog = PaymentsLog::request($paymentIntent,self::INCOMPLETE_STATUS,self::BUYING,$metadata);
                //for notifications
                $notificatioId = rand(15,35);
                $notification = new  UserNotification();
                // $notification->id = GuidHelper::getnotificationId();//(int)$notificatioId;
                $notification->type = 'Order Generated';
                $notification->notifiable_type = 'Order Generated';
                $notification->notifiable_id = Auth::user()->id;
                // $notification->uuid = GuidHelper::getGuid();//Auth::user()->id;
                //$notification->data = "You Order Has Been Generated click the link -> <a href='http://localhost:3000/orderdetail/".$order->orderid."' target='_blank'>".$order->orderid."</a>";
                $notification->data = "You Order Has Been Generated click the link -> <a href='https://notnew.testingwebsitelink.com/orderdetail/".$order->orderid."' target='_blank'>".$order->orderid."</a>";
                $notification->save();  
                $userCart = UserCart::where('user_id', '=' , Auth::user()->id)->delete();
                /** @var User $user */
                $user = Auth::user();
                $user->notify(new OrderPlaced($order));
            }
            $userCart = UserCart::where('user_id', '=' , Auth::user()->id)->delete();
            if($request->get('pm_card_Id')){
                $stripe = new StripeClient(env('STRIPE_SK'));
                $customer = $stripe->customers->create([
                    'payment_method' => $request->pm_card_Id, 
                    'email' => Auth::user()->email, 
                    'invoice_settings' => [
                        'default_payment_method' => $request->pm_card_Id,
                    ],
                ]);
                $stripeAmount=(int)ceil($totalOrderAmount);
                $intent = $stripe->paymentIntents->create([
                    'amount' => (int)($stripeAmount * 100),
                    'currency' => 'usd',
                    'customer' => $customer->id,
                    'payment_method' => $request->pm_card_Id, 
                    'confirm' => true,
                    'return_url' => 'https://example.com/payment/success', 
                ]);
            }
            if($order){
                return response()->json(['status'=> true,'data' =>"your Order has been Submited!"], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"Unable To Submit Order!"], 400);       
            }
        });
    }
    public function store_(Request $request)
    {    
        return DB::transaction(function () use ($request) {
            $order = new Order();
            $shipping = new ShippingDetail();
            $user = User::where('id', Auth::user()->id)->first();
            if($request->get("other_address") == true){
                // $shipping->fill($request->get("shippingDetail"));
                $shipping->user_id = Auth::user()->id;
                $shipping->name = $user->name;
                $shipping->street_address = $request->get("secondaddress");
                $shipping->state = $user->state_id;
                $shipping->city = $user->city_id;
                $shipping->zip = $request->get("zip");
                $shipping->save();
            }
            $shippingDetails = ShippingDetail::where('user_id',Auth::user()->id)->first();
            $order = new UserOrder();
            $order->orderid = GuidHelper::getShortGuid();
            $order->buyer_id = $request->get("buyer_id");
            $order->seller_id = 1;
            $order->payment_type = $request->get("payment_type");
            $order->billing_address = $shippingDetails->street_address;
            $order->fullname = $user->name;
            $order->phone = $user->phone;
            $order->address = $shippingDetails->street_address ? $shippingDetails->street_address: $request->get("secondaddress");
            $order->discountcode = $request->get("discountcode");
            $order->orderItems = json_encode($request->get("orderItems"));
            $order->subtotal_cost = $request->get("subtotal_cost") ? $request->get("subtotal_cost") : 0;
            $order->actual_cost = $request->get("actual_cost") ? $request->get("actual_cost") : 0;
            $order->shipping_cost = $request->get("shipping_cost") ? $request->get("shipping_cost") : 0;
            $order->prices = json_encode($request->get("prices"));
            $order->order_total = $request->get("order_total");
            $order->status= UserOrder::STATUS_PENDING;
            $order->payment_intents = $request->get("payment_intents");
            $order->Curency = $request->get("Curency");
            $order->order_type = $request->get("order_type");
            $order->shipping_detail_id = $shippingDetails->id;
            // $order->client_secret = $request->get("payment_intents");
            $order->created_at = Carbon::now()->toDateTimeString();//'2023-01-30 17:40:31';
            $order->updated_at = Carbon::now()->toDateTimeString();//'2023-01-30 17:40:31';
            $order->save();

            $orderItems = json_decode($request->get("orderItems"));
            foreach($orderItems as $items)
            {
                $usersummary = new UserOrderSummary();
                $usersummary->order_id = $order->id;
                $usersummary->product_id = $items->id;
                $usersummary->seller_id = $items->user_id;
                $usersummary->save();
            }

            if($request->get('payment_type') == 'Stripe'){
                $this->stripe = new StripeClient('sk_test_51McZZOBL2ne1CK3D89BPN3QmKiF2hMTZI1IvcdkgZ5asDQrOghL2IC3RnqAAsQK2ctgezVbCUdiwEfu9rv93Visf00eHdE1vlk');       
                $paymentIntent = $this->stripe->paymentIntents->create([
                    // 'amount' => $product->getPrice() * 100,
                    'amount' => $order->actual_cost * 100,
                    'currency' => 'usd',
                    // 'capture_method' => 'manual',
                    // 'transfer_group' => $order->Amount,
                    // 'transfer_data' => [
                    //     // 'amount' => $remaining,
                    //     'destination' => $product->user->stripe_account_id,
                    // ],
                ]);
    
                $payment_intents = $this->stripe->paymentIntents->retrieve(
                    $paymentIntent->id,
                    []
                  );
                  $order->update(
                    [
                        'payment_intents' => $payment_intents->id
                    ]
                );
                if($paymentIntent->status === 'requires_capture')
                {
                  $paymentIntent->capture($order->payment_intents);
                }
                $metadata = null;
                $paymentslog = PaymentsLog::request($paymentIntent,self::INCOMPLETE_STATUS,self::BUYING,$metadata);
                //for notifications
                $notificatioId = rand(15,35);
                $notification = new  UserNotification();
                // $notification->id = GuidHelper::getnotificationId();//(int)$notificatioId;
                $notification->type = 'Order Generated';
                $notification->notifiable_type = 'Order Generated';
                $notification->notifiable_id = Auth::user()->id;
                // $notification->uuid = GuidHelper::getGuid();//Auth::user()->id;
                //$notification->data = "You Order Has Been Generated click the link -> <a href='http://localhost:3000/orderdetail/".$order->orderid."' target='_blank'>".$order->orderid."</a>";
                $notification->data = "You Order Has Been Generated click the link -> <a href='https://notnew.apextechworldllc.com/orderdetail/".$order->orderid."' target='_blank'>".$order->orderid."</a>";
                $notification->save();  
                $userCart = UserCart::where('user_id', '=' , Auth::user()->id)->delete();
                /** @var User $user */
                $user = Auth::user();
                $user->notify(new OrderPlaced($order));
            }
            /** @var User $user */
            // $user = Auth::user();
            // $user->notify(new OrderPlaced($order));
            // dispatch(new captureFunds($order));
            // return depositStripeFund::dispatch()->onQueue('processing');
            if($order){
                return response()->json(['status'=> true,'data' =>"your Order has been Submited!"], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"Unable To Submit Order!"], 400);       
            }
        });
    }
    public function prices(){
        $finalPrices = [];
        $prices = Prices::select('name','value')->where('active', true)->get();
        foreach($prices as $key=> $price){
            $finalPrices[$key] = $price;
        }
        return $finalPrices;
    }
    public function getTrsutedUserData($userid){
       $user = User::where('id', $userid)->where('isTrustedSeller',true)->first();
       if($user){
           return TrustedSeller::where('user_id', $user->id)->first();
       }else{
           return null;
       }
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    { 
        return UserOrder::where('id', $id)->
        with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        },"buyer" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, "seller" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, 'shippingDetail' => function (BelongsTo $hasMany) {
            $hasMany->select(ShippingDetail::defaultSelect());
        }, 'refund' => function ($query) {
            $query->select(Refund::defaultSelect());
        }
        ])->get();
    }

public function getById($id,Request $request){
        $order= UserOrder::where('id', $id)->first();
        $data="";
        $status=$request->status??1;
        $orderStatus=$request->order_status??0;
        $orderStatusValue="";
        if($orderStatus==0){
            $orderStatusValue="pending";
        }
        else if($orderStatus==1){
            $orderStatusValue="accepted";
        }
        else if($orderStatus==2){
            $orderStatusValue="COMPLETED";
        }
        else if($orderStatus==3){
            $orderStatusValue="rejected";
        }
        
        if($status==1)
        {
            $total=0;
            $voucherDiscount=0;
            $vouchers=UserStoreVouchers::where('order_id',$id)->get();
            foreach ($vouchers as $voucher) {
                if($voucher){
                    $counpon=Coupon::where('code',$voucher->coupon_code)->first();
                    $voucherDiscount+=$counpon->discount??0;
                  }
            }
        $orderdetails = UserOrderDetails::where('order_id',$id)->where('status',$orderStatusValue)->get(); 
        $orderdetailscount = UserOrderDetails::where('order_id',$id)->where('status',$orderStatusValue)->count(); 
        if($request->refunded==1){
            $orderdetails = UserOrderDetails::where('order_id',$id)->where('status','COMPLETED')->where('refunded',1)->get(); 
            $orderdetailscount = UserOrderDetails::where('order_id',$id)->where('status','COMPLETED')->where('refunded',1)->count(); 
        }
        $orderdtls = [];
        foreach($orderdetails as $orderdetail){
            $product = Product::with('shop')->where('id', $orderdetail->product_id)->first();
            $orderdetail->new_attributes=$orderdetail->attributes;
            $correctedJsonString = str_replace(['{key:', 'value:'], ['{"key":', '"value":'], $orderdetail->attributes);
            $correctedJsonString = str_replace(['color', 'blue'], ['"color"', '"blue"'], $orderdetail->attributes);
            $correctedJsonString = str_replace(['}', ']'], ['}', ']'], $orderdetail->attributes);
            $attributesArray = json_decode($correctedJsonString, true);
            $refund=(object)[];
            if($request->refunded==1){
                $refund=Refund::with('media')->where('order_id',$id)->where('product_id',$orderdetail->product_id)->first();
                $refundedImages=[];
                foreach ($refund->media as $value) {
                    array_push($refundedImages,$value->name);
                }
                $refund->refundedImages=$refundedImages;
            }
            
            $orderdetails =[
                'id'=>$product->id,
                'seller'=>$product->shop->fullname,
                'name'=>$product->name,
                'producttotal'=>$orderdetail->price,
                'ordertotal'=>$orderdetail->price,
                'attributes' => $attributesArray,
                'quantity' => $orderdetail->quantity,
                'refund' => $refund,
                'guid' => $orderdetail->guid,
                'media'=>$product->media,
                "rating"=>$product->average_rating_count,
                "feedback"=>FeedBack::with('user','user.media')->where('product_id',$product->id)->get()
                ];
                array_push($orderdtls,$orderdetails);
                $total+=$orderdetail->price;
        }
        $data=[
            'id'=>$order->id,
            'orderid'=>$order->orderid,
            'latitude'=>$order->latitude,
            'longitude'=>$order->longitude,
            'shipmentaddress'=>$order->billing_address,
            'phone'=>$order->phone,
            'name'=>$order->fullname,
            'status'=>$order->status,
            'products'=>$orderdtls,
            'totalItems'=> $orderdetailscount,
            'subtotal'=>$total,
            'shippingcost'=>$order->shipping_cost,
            'voucher_discount'=>$voucherDiscount,
            'ordertotal'=>$total+$order->shipping_cost-$voucherDiscount,
            'admin_notes'=>$order->admin_notes
        ];
        return response()->json(['status'=> true,'data' => $data], 200); 
        }
        else
        {
            $seller=SellerData::where('user_id',Auth::user()->id)->first();
            $voucher=UserStoreVouchers::where('store_id',$seller->id)->where('order_id',$id)->first();
            $voucherDiscount=0;
            $orderdetails = UserOrderDetails::where('order_id',$id)->where('store_id',$seller->id)->where('status',$orderStatusValue)->get(); 
            $orderdetailscount = UserOrderDetails::where('order_id',$id)->where('store_id',$seller->id)->where('status',$orderStatusValue)->count();
          if($voucher){
            $counpon=Coupon::where('code',$voucher->coupon_code)->first();
            $voucherDiscount+=$counpon->discount??0;
          }
        if($request->refunded==1){
            $orderdetails = UserOrderDetails::where('order_id',$id)->where('refunded',1)->where('store_id',$seller->id)->where('status','COMPLETED')->get(); 
            $orderdetailscount = UserOrderDetails::where('order_id',$id)->where('refunded',1)->where('store_id',$seller->id)->where('status','COMPLETED')->count(); 
        }
        $orderdtls = [];
        $total=0;
        foreach($orderdetails as $orderdetail){
            $product = Product::with('shop')->where('id', $orderdetail->product_id)->first();
            $orderdetail->new_attributes=$orderdetail->attributes;
            $correctedJsonString = str_replace(['{key:', 'value:'], ['{"key":', '"value":'], $orderdetail->attributes);
            $correctedJsonString = str_replace(['color', 'blue'], ['"color"', '"blue"'], $orderdetail->attributes);
            $correctedJsonString = str_replace(['}', ']'], ['}', ']'], $orderdetail->attributes);
            $attributesArray = json_decode($correctedJsonString, true);
            $refund=(object)[];
            if($request->refunded==1){
                $refund=Refund::with('media')->where('order_id',$id)->where('product_id',$orderdetail->product_id)->first();
                $refundedImages=[];
                foreach ($refund->media as $value) {
                    array_push($refundedImages,$value->name);
                }
                $refund->refundedImages=$refundedImages;
            }
            
            $total+=$orderdetail->price;
            $orderdetails =[
                'id'=>$product->id,
                'seller'=>$product->shop->fullname,
                'name'=>$product->name,
                'producttotal'=>$orderdetail->price,
                'ordertotal'=>$orderdetail->price,
                'attributes' => $attributesArray,
                'quantity' => $orderdetail->quantity,
                'refund' => $refund,
                'guid' => $orderdetail->guid,
                'media'=>$product->media,
                "rating"=>$product->average_rating_count,
                "feedback"=>FeedBack::with('user','user.media')->where('product_id',$product->id)->get(),
                
                ];
                array_push($orderdtls,$orderdetails);
        }
        $data=[
            'id'=>$order->id,
            'orderid'=>$order->orderid,
            'latitude'=>$order->latitude,
            'longitude'=>$order->longitude,
            'shipmentaddress'=>$order->billing_address,
            'phone'=>$order->phone,
            'name'=>$order->fullname,
            'status'=>$order->status,
            'products'=>$orderdtls,
            'totalItems'=> $orderdetailscount,
            'subtotal'=>$total,
            'shippingcost'=>$order->shipping_cost,
            'voucher_discount'=>$voucherDiscount,
            'ordertotal'=>$total-$voucherDiscount,
            'admin_notes'=>$order->admin_notes
        ];
        return response()->json(['status'=> true,'data' => $data], 200);
        }
    }
    public function getById_($id){
        $order= UserOrder::where('id', $id)
                ->first();
        $orderdetails = UserOrderDetails::where('order_id',$id)->get(); 
        $orderdetailscount = UserOrderDetails::where('order_id',$id)->count(); 
        $orderdtls = [];
        $storeIds=[];
        foreach($orderdetails as $orderdetail){
            array_push($storeIds, $orderdetail->store_id);
        }
        $voucherDiscount=0;
            $vouchers=UserStoreVouchers::where('order_id',$id)->get();
            foreach ($vouchers as $voucher) {
                if($voucher){
                    $counpon=Coupon::where('code',$voucher->coupon_code)->first();
                    $voucherDiscount+=$counpon->discount??0;
                  }
            }
        $store_ids = array_unique($storeIds);
        $stores = [];
        foreach($orderdetails as $orderdetail){
            $store= SellerData::whereIn('id', $store_ids)->get();
            array_push($stores, $store);
        }
        $storeData = [];
        foreach($stores[0] as $store){
            $orderdetails = UserOrderDetails::where('store_id',$store->id)->first(); 
            $userdetailsOrds = UserOrderDetails::where('store_id',$store->id)->where('order_id',$id)->get();
            $pros = [];
            foreach($userdetailsOrds as $userdetailOrd){
                if($userdetailOrd->refunded == false){
                    $correctedJsonString = str_replace(['{key:', 'value:'], ['{"key":', '"value":'], $orderdetail->attributes);
                    $correctedJsonString = str_replace(['color', 'blue'], ['"color"', '"blue"'], $orderdetail->attributes);
                    $correctedJsonString = str_replace(['}', ']'], ['}', ']'], $orderdetail->attributes);
                    $attributesArray = json_decode($correctedJsonString, true);
                    $product = Product::with('shop')->where('id',$userdetailOrd->product_id)->first();
                    $data =[
                        "shopid"=>$product->shop->id,
                        "id"=> $product->id,
                        "name"=> $product->name,
                        "refunded"=> $userdetailOrd->refunded,
                        "producttotal"=>$product->price,
                        "ordertotal"=>$userdetailOrd->price,
                        "attributes" =>  $attributesArray,
                        "quantity" => $userdetailOrd->quantity,
                        "refunded" => $userdetailOrd->refunded,
                        'guid' => $userdetailOrd->guid,
                        'media'=>$product->media[0],
                    ];
                    array_push($pros, $data);
                }
            }
            $data="";
            if(count($pros) > 0){
                $data=[
                    "id"=> $store->id,
                    "name"=>$store->fullname,
                    "products"=>$pros
                ];
            }
            if($data){
                array_push($storeData, $data);    
            }
        }
        $data=[
                'id'=>$order->id,
                'orderid'=>$order->orderid,
                'latitude'=>$order->latitude,
                'longitude'=>$order->longitude,
                'shipmentaddress'=>$order->billing_address,
                'phone'=>$order->phone,
                'name'=>$order->fullname,
                'status'=>$order->status,
                'stores'=>$storeData,
                'totalItems'=> $orderdetailscount,
                'subtotal'=>$order->order_total,
                'shippingcost'=>$order->shipping_cost,
                'voucher_discount'=>$voucherDiscount,
                'ordertotal'=>$order->order_total+$order->shipping_cost-$voucherDiscount,
                'admin_notes'=>$order->admin_notes
            ];
        if($order){
            return response()->json(['status'=> true,'data' => $data], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 'unable to get order Details'], 400);        
        }
    }
       public function getRejectedOrdersCustomer(Request $request)
    {
        $orders = UserOrder::join('tbl_user_order_details', 'tbl_user_order.id', '=', 'tbl_user_order_details.order_id')
        ->where('tbl_user_order_details.status', 'rejected')
            ->where('buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id')->get();
        if ($orders) {
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->where('store_id',$seller->id)->first();
            }
            return response()->json(['status' => true, 'data' => $orders], 200);
        } else {
            return response()->json(['status' => false, 'data' => [], 'message' => 'No Rejected Orders'], 400);
        }
    }
      public function getAcceptedOrders(Request $request)
    {
        
        $seller = SellerData::where('user_id', Auth::user()->id)
            ->first();
        if (!$seller) {
            return response()->json(['status' => false, 'message' => "You haven't registered as a seller"], 400);
        }
        $orders = UserOrder::join('tbl_user_order_details', 'tbl_user_order.id', '=', 'tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'accepted')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
            $filteredOrders=[];
        if ($orders) {
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->where('store_id',$seller->id)->first();
                $totalOrd=UserOrderDetails::where('order_id',$order->order_id)->where('store_id', $seller->id)->where('tbl_user_order_details.status', 'accepted')->get();
                if(count($totalOrd)>0){
                    $filteredOrders[] = $order;
                }
            }
            return response()->json(['status' => true, 'data' => $filteredOrders,'message' => "Accepted Orders!"], 200);
        } else {
            return response()->json(['status' => false, 'data'=>[],'message' => "No Accepted Orders!"], 400);
        }
    }
    public function getAcceptedOrdersCustomer(Request $request)
    {
        $orders = UserOrder::join('tbl_user_order_details', 'tbl_user_order.id', '=', 'tbl_user_order_details.order_id')
        ->where('tbl_user_order_details.status', 'accepted')
            ->where('tbl_user_order.buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
        if ($orders) {
             foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->first();
            }
            return response()->json(['status' => true, 'data' => $orders,'message' => "Accepted Orders!"], 200);
        } else {
            return response()->json(['status' => false, 'data' => [], 'message' => 'No Accepted Orders'], 400);
        }
    }
     public function getRejectedOrders()
    {
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller)
        {
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);       
        }
        $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'rejected')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
        if($orders){
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->where('store_id',$seller->id)->first();
            }
            return response()->json(['status'=> true,'data' => $orders,'message' => 'Rejected Orders'], 200);       
        }else{
            return response()->json(['status'=> false,'data'=>[],'message' => 'No Rejected Orders'], 400);        
        }
    }
     public function updateOrderStatus(Request $request){
        $order= UserOrder::where('id', $request->order_id)->first();
        if(!$order){
            return response()->json(['status'=> false,'message' => "No Order Found!"], 400);
        }
        $seller = SellerData::where('user_id', Auth::user()->id)->first();
        if(!$seller)
        {
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);       
        }
        $status=$request->status;
        if($status=="1"){
            UserOrderDetails::where('order_id',$order->id)->where('store_id',$seller->id)->update([
                "status"=>"pending"
            ]);
        }
        elseif($status=="2"){
            UserOrderDetails::where('order_id',$order->id)->where('store_id',$seller->id)->update([
                "status"=>"rejected"
            ]);
            $arr=array(
                "title"=>"Your Order has been Rejetced by Seller!",
                "message"=>"Your Order has been Rejetced by Seller! Your Order # is ".$order->orderid,
                "user_id"=>$order->buyer_id,
                "type"=>"buying",
                "sender_id"=>$seller->user_id,
                "notification_type"=>"buying",
                "recieved_from"=>"",
                "product_guid"=>"",
                "room_id"=>"",
                "win"=>0
                );
                StripeHelper::saveNotification($arr);
                $sellerTransaction=SellerTransaction::where('order_id', $order->id)->where('seller_guid', $seller->guid)->where('type', 'Order')->first();
                $user = User::find($order->buyer_id);
                $user->wallet = $user->wallet + $sellerTransaction->amount;
                $user->save();
                WalletTransaction::create([
                    "amount" => $sellerTransaction->amount,
                    "type" => "refund",
                    "user_id" => $order->buyer_id,
                    "message" => "Refund of $" . $sellerTransaction->amount . " has been added to your Wallet! Against Order # " . $order->orderid,
                    "order_id" => $order->id
                ]);
                SellerTransaction::where('order_id', $order->id)->where('seller_guid', $seller->guid)->where('type', 'Order')->delete();
        }
        elseif($status=="3"){
            UserOrderDetails::where('order_id',$order->id)->where('store_id',$seller->id)->update([
                "status"=>"COMPLETED",
                'completed_timestamp'=>Carbon::now()
            ]);
            $arr=array(
                "title"=>"Your Order has been Completed by Seller!",
                "message"=>"Your Order has been Completed by Seller! Your Order # is ".$order->orderid,
                "user_id"=>$order->buyer_id,
                "type"=>"buying",
                "sender_id"=>$seller->user_id,
                "notification_type"=>"buying",
                "recieved_from"=>"",
                "product_guid"=>"",
                "room_id"=>"",
                "win"=>0
                );
                StripeHelper::saveNotification($arr);
        }
        elseif ($status == "4") {
            UserOrderDetails::where('order_id',$order->id)->where('store_id',$seller->id)->update([
                "status"=>"accepted"
            ]);
            $arr=array(
                "title"=>"Your Order has been Accepted by Seller!",
                "message"=>"Your Order has been Accepted by Seller! Your Order # is ".$order->orderid,
                "user_id"=>$order->buyer_id,
                "type"=>"buying",
                "sender_id"=>$seller->user_id,
                "notification_type"=>"buying",
                "recieved_from"=>"",
                "product_guid"=>"",
                "room_id"=>"",
                "win"=>0
                );
                StripeHelper::saveNotification($arr);
        }
        return response()->json(['status'=> true,'message' => "Order Status Updated Successfully!",'data'=>$order], 200);
    }
    public function active(Request $request){
        
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller)
        {
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);       
        }
        
        $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'pending')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id','DESC')->get();
        if($orders){
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->where('store_id',$seller->id)->first();
            }
            return response()->json(['status'=> true,'data' => $orders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 0], 400);        
        }
    }
    
    public function active_Customer(Request $request){
        
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        
        
            $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'pending')
            ->where('buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
        if($orders){
             foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->first();
            }
            return response()->json(['status'=> true,'data' => $orders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 0], 400);        
        }
    }
    public function completed(Request $request){
      
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller)
        {
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);       
        }
         $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
            $filteredOrders=[];
        if($orders){
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('order_id',$order->order_id)->where('tbl_user_order_details.status', 'COMPLETED')->where('store_id', $seller->id)->where('refunded',0)->first();
                $totalOrd=UserOrderDetails::where('order_id',$order->order_id)->where('store_id', $seller->id)->where('tbl_user_order_details.status', 'COMPLETED')->where('refunded',0)->get();
                if(count($totalOrd)>0){
                    $filteredOrders[] = $order;
                }
            }
            
            return response()->json(['status'=> true,'data' => $filteredOrders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => "Unable to get Orders"], 400);        
        }
    }
    public function completed_Customer_(Request $request){

        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        //  $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
        //     ->where('tbl_user_order.status', 'COMPLETED')
        //     ->where('store_id', $seller->id)
        //     //->where('buyer_id', Auth::user()->id)
        //     ->orWhere('buyer_id', Auth::user()->id)
        //     ->get(["tbl_user_order.id",  "buyer_id", "payment_type",
        //         "billing_address", "phone", "address", "discountcode","orderItems", "subtotal_cost",
        //         "actual_cost", "shipping_cost", "prices","order_total", "status", "deliver_status", "deliver_at",
        //         "payment_intents", "Curency", "admin_notes", "shipping_detail_id", "delivered_at",
        //         "customer_email_sent", "client_secret","shipment_paymentIntents", "shipment_clientSecret",
        //         "parcel_size","parcel_width","parcel_height", "parcel_length", "delivery_days","read_by_admin",
        //         "tbl_user_order.created_at", "tbl_user_order.updated_at","order_type","state","city"]);
        
            // $orders =DB::select("SELECT DISTINCT(`tbl_user_order`.id),orderid,status,buyer_id,payment_type, billing_address, seller_datas.fullname, discountcode,orderItems, subtotal_cost,
            //     actual_cost, shipping_cost, prices,order_total, status, deliver_status, deliver_at,
            //     payment_intents, Curency, admin_notes, shipping_detail_id, delivered_at,'seller' as user_tag,
            //     customer_email_sent, client_secret,shipment_paymentIntents, shipment_clientSecret,
            //     tbl_user_order.updated_at,country,order_type,state,city
            //     FROM `tbl_user_order` inner join tbl_user_order_details on `tbl_user_order`.id = tbl_user_order_details.order_id 
            //     inner join seller_datas on tbl_user_order_details.store_id = seller_datas.id
            //     where tbl_user_order_details.store_id =".$seller->id."  and status = 'COMPLETED'
            //     union ALL");
                $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order.status', 'COMPLETED')
            ->where('buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id'->orderBy('tbl_user_order.id', 'DESC'))->get();
           
               
                if($orders){
                    
                     foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->first();
            }
                    return response()->json(['status'=> true,'data' => $orders], 200);       
                }else{
                    return response()->json(['status'=> false,'message' => 'Unable to get Orders'], 400);        
                }
    }
    public function completed_Customer(Request $request){
       
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
       
      $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
            $filteredOrders = [];
        if($orders){
                   foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('tbl_user_order_details.status', 'COMPLETED')->where('order_id',$order->order_id)->where('refunded',0)->first();
                $totalOrd=UserOrderDetails::where('order_id',$order->order_id)->where('tbl_user_order_details.status', 'COMPLETED')->where('refunded',0)->get();
                if(count($totalOrd)>0){
                    $filteredOrders[] = $order;
                }
            }
            return response()->json(['status'=> true,'data' => $filteredOrders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 'Unable to get Orders'], 400);        
        }
    }
    public function createPaymentIntent(Request $request)
    {
        try{
            $stripe = new StripeClient(env('STRIPE_SK'));
            $paymentIntent=$stripe->paymentIntents->create([
                'amount' => $request->amount*100,
                'currency' => $request->currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]); 
            return response()->json(['status'=> true,'data' => $paymentIntent->client_secret,"message"=>"Payment Intent!"], 200); 
        }
        catch(\Exception $e)
        {
            return response()->json(['status'=> false,'message' =>$e->getMessage()], 400);        
        }
        $stripe = new StripeClient(env('STRIPE_SK'));
        $paymentIntent=$stripe->paymentIntents->create([
            'amount' => $request->amount*100,
            'currency' => $request->currency,
            'automatic_payment_methods' => ['enabled' => true],
        ]);   
    }
    public function createPaymentIntentTest(Request $request)
    {
        try{
            $stripe = new StripeClient("sk_test_51QFbMgAY9nuBdhI8WiWKOPcNyslmTnRs4jXxPLw9r3URDzOF79Iv0XWULkqnDZRsNnZkhxdcWQmjW7jPCAnsq98c00tQsXaLqX");
            $paymentIntent=$stripe->paymentIntents->create([
                'amount' => $request->amount*100,
                'currency' => $request->currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]); 
            return response()->json(['status'=> true,'data' => $paymentIntent->client_secret,"message"=>"Payment Intent!"], 200); 
        }
        catch(\Exception $e)
        {
            return response()->json(['status'=> false,'message' =>$e->getMessage()], 400);        
        }
          
    }
     public function refund_Customer(Request $request){
        
         $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
         ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('buyer_id', Auth::user()->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
            $filteredOrders = [];
        if($orders){
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('order_id',$order->order_id)->where('status', 'COMPLETED')->where('refunded',1)->first();
                $totalOrd=UserOrderDetails::where('order_id',$order->order_id)->where('tbl_user_order_details.status', 'COMPLETED')->where('refunded',1)->get();
                if(count($totalOrd)>0){
                    $filteredOrders[] = $order;
                }
            }
            return response()->json(['status'=> true,'data' => $filteredOrders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 0], 400);        
        }
    }
    public function refund(Request $request){
       
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller)
        {
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);       
        }
         $orders = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->orderBy('tbl_user_order.id', 'DESC')->get();
            $filteredOrders=[];
        if($orders){
            foreach($orders as $order){
                $order->details=UserOrderDetails::with('product','product.media')->where('id',$order->id)->where('store_id', $seller->id)->where('refunded',1)->where('status', 'COMPLETED')->first();
                $totalOrd=UserOrderDetails::where('order_id',$order->order_id)->where('store_id', $seller->id)->where('refunded',1)->where('status', 'COMPLETED')->get();
                if(count($totalOrd)>0){
                    $filteredOrders[] = $order;
                }
            }
            return response()->json(['status'=> true,'data' => $orders], 200);       
        }else{
            return response()->json(['status'=> false,'message' => 0], 400);        
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Order
     */
    // public function update(Order $order, Request $request){
        
    // }
    public function update__(Order $order, Request $request){
        try{
            return DB::transaction(function () use ($request, $order) { 
                $shouldUpdate = true;
                if ($request->has('status')) {
                    $stripe = new StripeClient(env('STRIPE_SK'));
                    $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));
                    if ($paymentIntent->id !== $request->get('payment_intent') || $paymentIntent->status !== 'requires_capture'){
                        $shouldUpdate = false;
                    }
                }
                // if ($shouldUpdate) {
                    $buyer = User::where('id', $order->buyer_id)->first();
                    $seller = User::where('id', $order->seller_id)->first();
                    $product = Product::where('id', $order->product_id)->first();
                    $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();

                    if($seller->isTrustedSeller == true){
                        $trustedseller = TrustedSeller::where('user_id',$order->seller_id)->first();
                        if($trustedseller->shipmenttype == "Fedex"){
                             $resp = array(
                                'labelResponseOptions' => "URL_ONLY",
                                'requestedShipment' => array(
                                    'shipper' => array(
                                        'contact' => array(
                                            "personName" => $seller->name,//"Shipper Name",
                                            "phoneNumber" => '1234567890'// $seller->phone,//1234567890,
                                             ),
                                        'address' => array(
                                            'streetLines' => array(
                                                $product->street_address,
                                            ),
                                            "city" =>$product->city,//"HARRISON",
                                            "stateOrProvinceCode" =>$product->state,//"AR",
                                            "postalCode" => $product->zip,//72601,
                                            "countryCode" => "US"
                                        )
                                    ),
                                    'recipients' => array(
                                        array(
                                            'contact' => array(
                                                "personName" => $buyer->name,//"BUYER NAME",
                                                "phoneNumber" => '1234567980'//$buyer->phone,//1234567890,
                                           ),
                                            'address' => array(
                                                'streetLines' => array(
                                                    $buyer_shipping->street_address,//"Recipient street address",
                                                ),
                                                "city" => $buyer_shipping->city,//"Collierville"
                                                "stateOrProvinceCode" => $buyer_shipping->state,//"TN"
                                                "postalCode" => $buyer_shipping->zip,//38017
                                                "countryCode" => "US"
                                            )
                                        ),
                                    ),
                                    'shippingChargesPayment' => array(
                                        "paymentType" => "SENDER"
                                    ),
                                    "shipDatestamp" => Carbon::today()->format('Y-m-d'),
                                    "serviceType" => "FEDEX_GROUND",//"FEDEX_GROUND",//"STANDARD_OVERNIGHT",
                                    "packagingType" => "YOUR_PACKAGING",//"YOUR_PACKAGING",//"FEDEX_PK",
                                    "pickupType" => "USE_SCHEDULED_PICKUP",//"CONTACT_FEDEX_TO_SCHEDULE",//"USE_SCHEDULED_PICKUP",
                                    // "carrierCode"=> "FXSP",
                                    "blockInsightVisibility" => false,
                                    'labelSpecification' => array(
                                        "labelRotation"=> "UPSIDE_DOWN",
                                        "imageType"=> "PDF",
                                        "labelPrintingOrientation"=> "TOP_EDGE_OF_TEXT_FIRST",
                                        "returnedDispositionDetail"=> true,
                                        "labelStockType" => "PAPER_85X11_TOP_HALF_LABEL"
                                    ),
                                    'requestedPackageLineItems' => array(
                                        array(
                                            'weight' => array(
                                                "value" => $product->weight,//10,
                                                "units" => "LB"
                                            )
                                        ),
                                    ),
                                ),
                                'accountNumber' => array(
                                    "value" => "740561073"
                                ),
                            );
                            
                            $fedex_shipment = Fedex::createShipment($resp);
                            $req = $request->all();
                            if (isset($fedex_shipment["errors"])) {
                                
                                throw new \Exception($fedex_shipment["errors"][0]['message'], 1);
                                
                            } else if (isset($fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"])) {

                                $metadata = null;
                                $req["tracking_id"] = $fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"];
                                $req["fedex_shipping"] = json_encode($fedex_shipment);
                                $order->fill($req);
                                $order->update();
                                $paymentmode = "Incomplete";
                                $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                                $product->is_sold = true;
                                $product->update();
                                // Artisan::call('queue:work  --daemon');
                                // @Todo: create a different controller action for order confirmation
                                if ($request->has('status')) {
                                    /** @var User $user */
                                    $user = Auth::user();
                                    $user->notify(new OrderPlaced($order));
                                    $seller->notify(new OrderPlacedSeller($order));
                                }
                                // depositStripeFund::dispatch();
                                // Artisan::call('schedule:run');
                    
                            }
                        // }
                        }else if($trustedseller->shipmenttype == "USPS"){
                                $resp = '<CarrierPickupScheduleRequest USERID="974FLEXM7409">
                                <FirstName>'. $buyer->name .'</FirstName>
                                <LastName>'. $buyer->name .'</LastName>
                                <FirmName>NotNew</FirmName>
                                <SuiteOrApt>Suite 101</SuiteOrApt>
                                <Address2>'. $buyer_shipping->street_address .'</Address2>
                                <Urbanization></Urbanization>
                                <City>'. $buyer_shipping->city .'</City>
                                <State>'.  $buyer_shipping->state .'</State>
                                <ZIP5>'. $buyer_shipping->zip .'</ZIP5>
                                <ZIP4>1000</ZIP4>
                                <Phone>5555551234</Phone>
                                <Extension></Extension>
                                <Package>
                                <ServiceType>PriorityMailExpress</ServiceType>
                                <Count>2</Count>
                                </Package>
                                <Package>
                                <ServiceType>PriorityMail</ServiceType>
                                <Count>1</Count>
                                </Package>
                                <EstimatedWeight>14</EstimatedWeight>   
                                <PackageLocation>Front Door</PackageLocation>
                                <SpecialInstructions>Packages are behind the screen door.</SpecialInstructions>
                            </CarrierPickupScheduleRequest>';
                    
                            $_shipment = USPS::createShipment($resp);
                            
                            $req = $request->all();
                            if (isset($_shipment["errors"])) {
                                
                                throw new \Exception($_shipment["errors"][0]['message'], 1);
                                
                            } else if (isset($_shipment["ConfirmationNumber"])) {

                                $metadata = null;
                                $req["tracking_id"] = $_shipment["ConfirmationNumber"];
                                $req["fedex_shipping"] = json_encode($_shipment);
                                $order->fill($req);
                                $order->update();
                                $paymentmode = "Incomplete";
                                $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                                $product->is_sold = true;
                                $product->update();
                                // Artisan::call('queue:work  --daemon');
                                // @Todo: create a different controller action for order confirmation
                                if ($request->has('status')) {
                                    /** @var User $user */
                                    $user = Auth::user();
                                    $user->notify(new OrderPlaced($order));
                                    $seller->notify(new OrderPlacedSeller($order));
                                }
                                // depositStripeFund::dispatch();
                                // Artisan::call('schedule:run');
                    
                            }
                        // }
                        }
                    }else{
                        $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();
                        $resp = array(
                            'labelResponseOptions' => "URL_ONLY",
                            'requestedShipment' => array(
                                'shipper' => array(
                                    'contact' => array(
                                        "personName" => $seller->name,//"Shipper Name",
                                        "phoneNumber" => '1234567890'// $seller->phone,//1234567890,
                                    ),
                                    'address' => array(
                                        'streetLines' => array(
                                            $product->street_address,
                                        ),
                                        "city" =>$product->city,//"HARRISON",
                                        "stateOrProvinceCode" =>$product->state,//"AR",
                                        "postalCode" => $product->zip,//72601,
                                        "countryCode" => "US"
                                    )
                                ),
                                'recipients' => array(
                                    array(
                                        'contact' => array(
                                            "personName" => $buyer->name,//"BUYER NAME",
                                            "phoneNumber" => '1234567980'//$buyer->phone,//1234567890,
                                        ),
                                        'address' => array(
                                            'streetLines' => array(
                                                $buyer_shipping->street_address,//"Recipient street address",
                                            ),
                                            "city" => $buyer_shipping->city,//"Collierville"
                                            "stateOrProvinceCode" => $buyer_shipping->state,//"TN"
                                            "postalCode" => $buyer_shipping->zip,//38017
                                            "countryCode" => "US"
                                        )
                                    ),
                                ),
                                'shippingChargesPayment' => array(
                                    "paymentType" => "SENDER"
                                ),
                                "shipDatestamp" => Carbon::today()->format('Y-m-d'),
                                "serviceType" => "FEDEX_GROUND",//"FEDEX_GROUND",//"STANDARD_OVERNIGHT",
                                "packagingType" => "YOUR_PACKAGING",//"YOUR_PACKAGING",//"FEDEX_PK",
                                "pickupType" => "USE_SCHEDULED_PICKUP",//"CONTACT_FEDEX_TO_SCHEDULE",//"USE_SCHEDULED_PICKUP",
                                // "carrierCode"=> "FXSP",
                                "blockInsightVisibility" => false,
                                'labelSpecification' => array(
                                    "labelRotation"=> "UPSIDE_DOWN",
                                    "imageType"=> "PDF",
                                    "labelPrintingOrientation"=> "TOP_EDGE_OF_TEXT_FIRST",
                                    "returnedDispositionDetail"=> true,
                                    "labelStockType" => "PAPER_85X11_TOP_HALF_LABEL"
                                ),
                                'requestedPackageLineItems' => array(
                                    array(
                                        'weight' => array(
                                            "value" => $product->weight,//10,
                                            "units" => "LB"
                                        )
                                    ),
                                ),
                            ),
                            'accountNumber' => array(
                                "value" => "740561073"
                            ),
                        );
                        
                        $fedex_shipment = Fedex::createShipment($resp);
                        $req = $request->all();
                        if (isset($fedex_shipment["errors"])) {
                            throw new \Exception($fedex_shipment["errors"][0]['message'], 1);
                        } else if (isset($fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"])) {
                            $metadata = null;
                            $req["tracking_id"] = $fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"];
                            $req["fedex_shipping"] = json_encode($fedex_shipment);
                            $order->fill($req);
                            $order->update();
                            $paymentmode = "Incomplete";
                            $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                            $product->is_sold = true;
                            $product->update();
                            // Artisan::call('queue:work  --daemon');
                            // @Todo: create a different controller action for order confirmation
                            if ($request->has('status')) {
                                /** @var User $user */
                                $user = Auth::user();
                                $user->notify(new OrderPlaced($order));
                                $seller->notify(new OrderPlacedSeller($order));
                            }
                            // depositStripeFund::dispatch();
                            // Artisan::call('schedule:run');
                        }
                    // }
                    }
            });
        }
        catch(Exception $e) {
            throw $e;
        }finally{
            $stripe = new StripeClient(env('STRIPE_SK'));
            $seller = User::where('id', $order->seller_id)->first();
            $account = $stripe->accounts->retrieve(
                $seller->stripe_account_id,
                []
              );
              if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
              {
                $user = Auth::user();
                $user->notify(new DepositAccount($order));
              }else{
                if($seller->isTrustedSeller == true){
                    //Artisan::call('capture:vendorfunds');
                }else{
                   //Artisan::call('capture:funds');
                }
                return $order;
              }
       }
    }
    public function update1(Order $order, Request $request)
    {
       
        $stripe = new StripeClient(env('STRIPE_SK'));
        $seller = User::where('id', $order->seller_id)->first();
        $account = $stripe->accounts->retrieve(
            $seller->stripe_account_id,
            []
          );
          if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
          {
            $user = Auth::user();
            $user->notify(new DepositAccount($order));
          }else{
            
            if($seller->isTrustedSeller == true){
              // Artisan::call('capture:vendorfunds');
            }else{
                //Artisan::call('capture:funds');
            }
            return $order;
          }
        }
        public function customerOrderCompCount(Request $request){
             return UserOrder::where('buyer_id', Auth::user()->id)
                 ->where('status', UserOrder::COMPLETED)
                 ->count();
         }

         public function customerOrderPendCount(Request $request){
            return UserOrder::where('buyer_id', Auth::user()->id)
                ->where('status', UserOrder::STATUS_PENDING)
                ->count();
        }

        public function customerOrderRefundCount(Request $request){
            return UserOrder::where('buyer_id', Auth::user()->id)
                ->where('status', UserOrder::REFUND)
                ->count();
        }

        public function customerOngoingOrders(Request $request){
            return UserOrder::where('buyer_id', Auth::user()->id)
                ->where('status', UserOrder::STATUS_PENDING)
                ->get();
        }
        public function customerCompletedOrders(Request $request){
            return UserOrder::where('buyer_id', Auth::user()->id)
                ->where('status', UserOrder::COMPLETED)
                ->get();
        }
        public function customerRefundOrders(Request $request){
            return UserOrder::where('buyer_id', Auth::user()->id)
                ->where('status', UserOrder::REFUND)
                ->get();
        }
        public function buyAgainOrders(Request $request){
            $order = UserOrderSummary::where('seller_id', Auth::user()->id)
            ->with(['buyer', 'product', 'order'])
            ->get();
            // $order = UserOrder::where('buyer_id', Auth::user()->id)
            // ->get();
            // $detailsDate = array();
            // foreach($order as $ord){
            //     array_push($detailsDate, $ord->created_at);
            // }
            // $detailOrders = array();
            // $dtailOrders = array();
            // $finalArray = array();
            // foreach($detailsDate as $date){
            //     $order = UserOrder::where('created_at', $date)
            //     ->first();
            //     array_push($dtailOrders, $order);
            //     $detailOrders =[
            //         'date' => $date,
            //         'orders' => $dtailOrders
            //     ];
            //     // array_push($finalArray, $detailOrders);
            // }
            // return $detailOrders;
            if($order){
                return response()->json(['status'=> true,'data' => $order], 200);       
            }else{
                return response()->json(['status'=> false,'message' => 'Unable to get Order'], 500);        
            }
        }
        public function updateSeller(Request $request, $id){
            return DB::transaction(function () use ($request, $id) { 
                UserOrder::where('id', $id)->update([
                    "estimateDelivery" => $request->get('estimateDelivery'),
                    "shipping_cost" => $request->get('shipping_cost'),
                    "discountcode" => $request->get('discountcode'),
                    "status"  => $request->get('pending'),
                    "admin_notes" => $request->get('admin_notes'),
                ]);
                return ['success' => true,'message' => 'Data Updated'];
            });

        }
        public function update(Order $order, Request $request)
        {
        // try{
        //     return DB::transaction(function () use ($request, $order) { 
               
        //         $shouldUpdate = true;
        //         if ($request->has('status')) {
                    
        //             $stripe = new StripeClient(env('STRIPE_SK'));
        //             $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));
                  
        //             if ($paymentIntent->id !== $request->get('payment_intent') || $paymentIntent->status !== 'requires_capture'){
                         
        //                 $shouldUpdate = false;
        //             }   
        //         }
        //         // if ($shouldUpdate) {
        //             $buyer = User::where('id', $order->buyer_id)->first();
        //             $seller = User::where('id', $order->seller_id)->first();
        //             $product = Product::where('id', $order->product_id)->first();
        //             $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();
                
        //             $resp = array(
        //                 'shipment' => array(
        //                     'from_address' => array(
        //                             'name' =>  $seller->name,
        //                             'street1' => $product->street_address,
        //                             "city" =>$product->city,//"HARRISON",
        //                             "state" =>$product->state,//"AR",
        //                             "zip" => $product->zip,//72601,
        //                             "country" => "US",
        //                             "phone" => "3331114444",
        //                             "email" => $seller->email
        //                         ),
        //                     'to_address' => array(
        //                             'name' =>  $buyer->name,
        //                             'street1' => $buyer_shipping->street_address,
        //                             "city" => $buyer_shipping->city,//"HARRISON",
        //                             "state" => $buyer_shipping->state,//"AR",
        //                             "zip" => $buyer_shipping->zip,//72601,
        //                             "country" => "US",
        //                             "phone" => "3331114444",
        //                             "email" => $buyer->email
        //                         ),
        //                         // "shipDatestamp" => Carbon::today()->format('Y-m-d'),
        //                     'parcel' => array(
        //                         'length' => $product->length,
        //                         'width' => $product->width,
        //                         "height" => $product->height,
        //                         "weight" => $product->weight,
        //                     ),
        //                 )
        //             );
        //             $req = $request->all();
                    
        //             if($seller->isTrustedSeller == true){
                        
        //                 $metadata = null;
        //                 $trustedSeller = TrustedSeller::where('user_id', $order->seller_id)->first();
        //                 $order->vendorshipmenttype = $trustedSeller->shipmenttype;
        //                 $order->vendorstatus = 'pending';
        //                 $order->fill($req);
        //                 $order->update();
        //                 $product->is_sold = true;
        //                 $product->update();
        //                 $paymentmode = "Incomplete";
        //                 $paymentslog = PaymentsVendorLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
        //                 if ($request->has('status')) {
        //                     /** @var User $user */
        //                     $user = Auth::user();
        //                     $user->notify(new OrderPlaced($order));
        //                     /**
        //                      * For Seller
        //                      */
        //                      $seller->notify(new OrderPlacedSeller($order));
        //                 }
        //             }else{
                        
        //                 $shipment = EasyPost::createShipment($resp);
                      
        //                 $shipment= json_decode($shipment);
        //                  if(isset($shipment->tracking_code)) {
        //                     $metadata = null;
        //                     $req["tracking_id"] = $shipment->tracking_code;
        //                     $req["fedex_shipping"] = json_encode($shipment);//json_encode();
        //                     $order->shipping_rates = $shipment->selected_rate->rate;
        //                     $order->fill($req);
        //                     $order->update();
        //                     $paymentmode = "Incomplete";
        //                     $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
        //                     $product->is_sold = true;
        //                     $product->update();
        //                     // Artisan::call('queue:work  --daemon');
        //                     // @Todo: create a different controller action for order confirmation
        //                     if ($request->has('status')) {
        //                         /** @var User $user */
        //                         $user = Auth::user();
        //                         $notify =  $user->notify(new OrderPlaced($order));
                               
        //                         /**
        //                          * For Seller
        //                          */
        //                          $seller->notify(new OrderPlacedSeller($order));
        //                     }
        //                 }else{
        //                     throw new \Exception($shipment, 1);
        //                 }
        //             }
        //             $stripe = new StripeClient(env('STRIPE_SK'));
        //             $seller = User::where('id', $order->seller_id)->first();
        //             $account = $stripe->accounts->retrieve(
        //                 $seller->stripe_account_id,
        //                 []
        //             );
                    
        //             if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
        //             {
                        
        //                 $user = Auth::user();
        //                 $user->notify(new DepositAccount($order));
        //                 // $seller->notify(new OrderPlacedSeller($order));
        //             }else{
        //                 // if($seller->isTrustedSeller == true){
        //                     // Artisan::call('capture:vendorfunds');
        //                 // // }else{
        //                     // Artisan::call('capture:funds');
        //                     // Artisan::call('capture:vendorfunds');
        //                 // }
        //                 return $order;
        //             }
        //     });
           
        // }
        // catch(Exception $e) {
        //     throw $e;
        // }
    }
        public function update_1(Order $order, Request $request)
        {
        try{
            return DB::transaction(function () use ($request, $order) { 
                
                $shouldUpdate = true;
                if ($request->has('status')) {
                    
                    $stripe = new StripeClient(env('STRIPE_SK'));
                    $paymentIntent = $stripe->paymentIntents->retrieve($request->get('payment_intent'));
                  
                    if ($paymentIntent->id !== $request->get('payment_intent') || $paymentIntent->status !== 'requires_capture'){
                         
                        $shouldUpdate = false;
                    }
                        
                }
    
                // if ($shouldUpdate) {
                    $buyer = User::where('id', $order->buyer_id)->first();
                    $seller = User::where('id', $order->seller_id)->first();
                    $product = Product::where('id', $order->product_id)->first();
                    $buyer_shipping = ShippingDetail::where('id', $order->shipping_detail_id)->first();
                    //$shipping_size = ShippingSize::where('id', $product->shipping_size_id)->first();
                    //    $product_shipping_detail = ProductShippingDetail::where('user_id', $order->seller_id)->where('product_id', $order->product_id)->first();
            
                    $resp = array(
                        'labelResponseOptions' => "URL_ONLY",
                        'requestedShipment' => array(
                            'shipper' => array(
                                'contact' => array(
                                    "personName" => $seller->name,//"Shipper Name",
                                    "phoneNumber" => '1234567890'// $seller->phone,//1234567890,
                                    // "companyName" => "Shipper Company Name"
                                ),
                                'address' => array(
                                    'streetLines' => array(
                                        $product->street_address,
                                    ),
                                    // 'streetLines'=> [
                                    //     '10 FedEx Parkway',
                                    //     'Suite 302'
                                    // ],
                                    "city" =>$product->city,//"HARRISON",
                                    "stateOrProvinceCode" =>$product->state,//"AR",
                                    "postalCode" => $product->zip,//72601,
                                    "countryCode" => "US"
                                )
                            ),
                            'recipients' => array(
                                array(
                                    'contact' => array(
                                        "personName" => $buyer->name,//"BUYER NAME",
                                        "phoneNumber" => '1234567980'//$buyer->phone,//1234567890,
                                        // "companyName" => "Recipient Company Name"
                                    ),
                                    'address' => array(
                                        'streetLines' => array(
                                            $buyer_shipping->street_address,//"Recipient street address",
                                        ),
                                        // 'streetLines' => [
                                        //     '10 FedEx Parkway',
                                        //     'Suite 302'
                                        //   ],
                                        "city" => $buyer_shipping->city,//"Collierville"
                                        "stateOrProvinceCode" => $buyer_shipping->state,//"TN"
                                        "postalCode" => $buyer_shipping->zip,//38017
                                        "countryCode" => "US"
                                    )
                                ),
                            ),
                            'shippingChargesPayment' => array(
                                "paymentType" => "SENDER"
                            ),
                            "shipDatestamp" => Carbon::today()->format('Y-m-d'),
                            "serviceType" => "FEDEX_GROUND",//"FEDEX_GROUND",//"STANDARD_OVERNIGHT",
                            "packagingType" => "YOUR_PACKAGING",//"YOUR_PACKAGING",//"FEDEX_PK",
                            "pickupType" => "USE_SCHEDULED_PICKUP",//"CONTACT_FEDEX_TO_SCHEDULE",//"USE_SCHEDULED_PICKUP",
                            // "carrierCode"=> "FXSP",
                            "blockInsightVisibility" => false,
                            'labelSpecification' => array(
                                "labelRotation"=> "UPSIDE_DOWN",
                                "imageType"=> "PDF",
                                "labelPrintingOrientation"=> "TOP_EDGE_OF_TEXT_FIRST",
                                "returnedDispositionDetail"=> true,
                                "labelStockType" => "PAPER_85X11_TOP_HALF_LABEL"
                            ),
                            'requestedPackageLineItems' => array(
                                array(
                                    'weight' => array(
                                        "value" => $product->weight,//10,
                                        "units" => "LB"
                                    )
                                ),
                                // array(
                                //     'dimensions' => array(
                                //         "length" => "100",
                                //         "width" => "50",
                                //         "height" => "30",
                                //         "units"=> "CM"
                                //     )
                                // ),
                            ),
                        ),
                        'accountNumber' => array(
                            "value" => "740561073"
                        ),
                    );
                    $req = $request->all();
                    if($seller->isTrustedSeller == true){
                        $metadata = null;
                        $trustedSeller = TrustedSeller::where('user_id', $order->seller_id)->first();
                        $order->vendorshipmenttype = $trustedSeller->shipmenttype;
                        $order->vendorstatus = 'pending';
                        $order->fill($req);
                        $order->update();
                        $product->is_sold = true;
                        $product->update();
                        $paymentmode = "Incomplete";
                        $paymentslog = PaymentsVendorLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                        if ($request->has('status')) {
                            /** @var User $user */
                            $user = Auth::user();
                            $user->notify(new OrderPlaced($order));
                            $seller->notify(new OrderPlacedSeller($order));
                        }
                    }else{
                        $fedex_shipment = Fedex::createShipment($resp);
                        
                        if (isset($fedex_shipment["errors"])) {
                            
                            throw new \Exception($fedex_shipment["errors"][0]['message'], 1);
                            
                        } else if (isset($fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"])) {

                            $metadata = null;
                            $req["tracking_id"] = $fedex_shipment["output"]["transactionShipments"][0]["masterTrackingNumber"];
                            $req["fedex_shipping"] = json_encode($fedex_shipment);
                            $order->fill($req);
                            $order->update();
                            $paymentmode = "Incomplete";
                            $paymentslog = PaymentsLog::request($paymentIntent,$req['status'],$paymentmode, $metadata);
                            $product->is_sold = true;
                            $product->update();
                            // Artisan::call('queue:work  --daemon');
                            // @Todo: create a different controller action for order confirmation
                            if ($request->has('status')) {
                                /** @var User $user */
                                $user = Auth::user();
                                $user->notify(new OrderPlaced($order));
                                $seller->notify(new OrderPlacedSeller($order));
                            }
                            // depositStripeFund::dispatch();
                            // Artisan::call('schedule:run');
                
                        }
                    // }
                    }
            });
        }
        catch(Exception $e) {
            throw $e;
        }
        finally{
            $stripe = new StripeClient(env('STRIPE_SK'));
            $seller = User::where('id', $order->seller_id)->first();
            $account = $stripe->accounts->retrieve(
                $seller->stripe_account_id,
                []
              );
              if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
              {
                $user = Auth::user();
                $user->notify(new DepositAccount($order));
              }else{
                
                // if($seller->isTrustedSeller == true){
                    // Artisan::call('capture:vendorfunds');
                // }else{
                    // Artisan::call('capture:funds');
                    // Artisan::call('capture:vendorfunds');
                // }
                return $order;
              }
       }
    }

    public function getSellerDashboardStats()
    {
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        if(!$seller){
            return response()->json(['status'=> false,'message' => "You haven't registered as a seller"], 400);
        }
        $completed = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->count();
        $earnings = UserOrder::join('tbl_user_order_details','tbl_user_order.id','=','tbl_user_order_details.order_id')
            ->where('tbl_user_order_details.status', 'COMPLETED')
            ->where('store_id', $seller->id)->groupBy('tbl_user_order.id')->sum('order_total');
            $totalCoupons = Coupon::where('seller_guid', $seller->guid)->count();
            $data =[
                'seller_guid'=>$seller->guid,
                'earnings'=> $earnings,
                'offers'=>$totalCoupons,
                'completed_orders_count'=> $completed
                ];
            return response()->json(['status'=> true,'data' => $data], 200); 
        
    }
   
    public function fedexRateCalculator(Request $request){
       $data = $request->all();
    //    return \Auth::user();
       $req='{
            "accountNumber": {
            "value": "740561073"
            },
            "requestedShipment": {
            "shipper": {
                "address": {
                "postalCode": 65247,
                "countryCode": "US"
                }
            },
            "recipient": {
                "address": {
                "postalCode": '. $data[0]['shipping_detail']['zip'] .',
                "countryCode": "US"
                }
            },
            "pickupType": "USE_SCHEDULED_PICKUP",
            "rateRequestType": [
                "ACCOUNT",
                "LIST"
            ],
            "requestedPackageLineItems": [
                {
                "weight": {
                    "units": "LB",
                    "value": 10
                }
                }
            ]
            }
        }';
        $fedex_shipment = Fedex::calculateRate(Fedex::rateCalculator($data));
        return $fedex_shipment;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function verifyAddressEasyPost(Request $request){
        $data = $request->all();
        $validateAddress = EasyPost::verifyAddress($data);
        return $validateAddress;
    }
    public function validatePostalCode(Request $request){
        /**
         * For FedEx
         */
        $data = $request->all();
        
        $validatePostalcode = Fedex::validatePostalCode($data);
       
        if (isset($validatePostalcode["errors"])) {
            throw new \Exception($validatePostalcode["errors"][0]['message'], 1);
        }else{
            return $validatePostalcode;
        }


    }
    public function delivered(Request $request, $id){
       $order = Order::where('id', $id)->update([
            'vendorstatus' => 'Delivered'
        ]);
        return "Order Delivered";
    }
    public function notdelivered(Request $request, $id){
        $order = Order::where('id', $id)->update([
            'vendorstatus' => 'Not Delivered'
        ]);
        return "Order Not Delivered";
    }

    public function validateAddress(Request $request){
        
        $requests = $request->all();
        $data = '<AddressValidateRequest USERID="974FLEXM7409">
                    <Revision>1</Revision>
                    <Address ID="0">
                        <Address1></Address1>
                        <Address2>'. $requests['street_address'] .'</Address2>
                        <City>Silver Spring</City>
                        <State>'. $requests['state'] .'</State>
                        <Zip5>'. $requests['zip'].'</Zip5>
                        <Zip4/>
                    </Address>
                </AddressValidateRequest>';
        $data0 = '<AddressValidateRequest USERID="974FLEXM7409">
                    <Revision>1</Revision>
                    <Address ID="0">
                        <Address1></Address1>
                        <Address2>FlexMarket Corp 935 Swinks Mill RD</Address2>
                        <City>Silver Spring</City>
                        <State>MD</State>
                        <Zip5>10002</Zip5>
                        <Zip4/>
                    </Address>
                </AddressValidateRequest>';
        $data__ ='<ZipCodeLookupRequest USERID="974FLEXM7409">
                    <Address ID="1">
                    <Address1></Address1>
                    <Address2>9600 colesville road</Address2>
                    <City>Silver Spring</City>
                    <State>MD</State>
                    <Zip5>20901</Zip5>
                    <Zip4></Zip4>
                    </Address>
                </ZipCodeLookupRequest>';

        $data_ ='<CityStateLookupRequest USERID="974FLEXM7409">
                    <City>Akron     </City>
                    <State></State>
                    <ZipCode ID="0">
                        <Zip5>10002</Zip5>
                    </ZipCode>
                </CityStateLookupRequest>';
        $validatePostalcode = USPS::validateAddress($data);
        
        if (isset($validatePostalcode["errors"])) {
            throw new \Exception($validatePostalcode["errors"][0]['message'], 1);
        }else{
            return $validatePostalcode;
        }
    }
    public function tracking($data)
    {
        /**
     * Fedex Start
     */
        /**
         * '111111111111' Test Tracking NO from Fedex
         */
        //     $track ="";
        //    if($data == self::FEDEXTESTSENTTRACKING){
        //     //For Shipment Sent to Fedex
        //         $trackingNo = self::FEDEXTESTSENTTRACKING;
        //         $track = Fedex::trackShipment(Fedex::trackPayload($trackingNo));
        //     }else if($data == self::FEDEXTESTDELIVEREDTRACKING){
        //         //For Testing Delivered
        //         $trackingNo =self::FEDEXTESTDELIVEREDTRACKING;
        //         $track = Fedex::trackShipment(Fedex::trackPayload($trackingNo));
        //    }else{
        //     $trackingNo = Order::where("tracking_id",$data)->get();
        //     $track = Fedex::trackShipment(Fedex::trackPayload($trackingNo[0]['tracking_id']));
        //     }
            
        //     return $track;
    /**
     * Fedex Ends
     */
    /**
     * For Easy Post
     */
        $track = EasyPost::trackShipment($data);
        return $track;
    }

    public function packed($data){
        $trackingNo = Order::find($data)->get();
        $track = Fedex::packed($trackingNo[0]['tracking_id']);
        return $track;
    }

    public function ratecalculator(Request $request){
        $data = $request->all();
       
        // $dataO = '<RateV4Request USERID="974FLEXM7409">
        //             <Revision>2</Revision>
        //             <Package ID="0">
        //             <Service>USPS Retail Ground</Service>
        //             <ZipOrigination>'. $data['shipperZip'] .'</ZipOrigination>
        //             <ZipDestination>'. $data['recipetZip'] .'</ZipDestination>
        //             <Pounds>'. $data['shipperweight'] .'</Pounds>
        //             <Ounces>'. $data['ounces'].'</Ounces>
        //             <Container></Container>
        //             <Width></Width>
        //             <Length></Length>
        //             <Height></Height>
        //             <Girth></Girth>
        //             <Machinable>TRUE</Machinable>
        //             </Package>
        //         </RateV4Request>';
            // For USPS Start
            // $returnRate = USPS::rateCalculator($dataO);
            // return $returnRate['Package']['Postage']['Rate'];
            // For USPS Ends
         // For FEDEX Start
        // return Fedex::rateCalculator(Fedex::payloadTrackRate($data));
        // $returnRate = Fedex::rateCalculator($data);
        
        // $rateReplyDetails = $returnRate['output']['rateReplyDetails'];
        // $rateDetail = [];
        // foreach($rateReplyDetails as $rateDetails){
        //     if($rateDetails['serviceType'] == self::SERVICETYPE){
        //         array_push($rateDetail, $rateDetails['ratedShipmentDetails'][0]['totalNetFedExCharge']);
        //     }
        // }
        // return $rateDetail;
        // For FEDEX Ends  
        //EasyPost Start
        $rates = EasyPost::shipmentRates($data);
        return $rates;
        //EasyPost Ends
    }
    public function testingShipping(Request $request)
    {
        
        $delivery_company=$request->delivery_company;
        $carrier="se-6537304";
        $from_country=$request->from_country??"US";
        $to_country=$request->to_country??"US";
        $from_postal_code=$request->from_postal_code??"10005";
        $to_postal_code=$request->to_postal_code??"91521";
        $weight=$request->weight??13;
        if($delivery_company==3)
        {
            $carrier="se-6325026";
        }
         if($delivery_company==5 || $delivery_company==7)
        {
            $carrier="se-6325024";
        }
         if($delivery_company==6)
        {
            $carrier="se-6537305";
        }
        
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.shipengine.com/v1/rates/estimate',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
	"carrier_ids": [
		"'.$carrier.'"
	],
	"from_country_code": "'.$from_country.'",
	"from_postal_code": "'.$from_postal_code.'",
	"to_country_code": "'.$to_country.'",
	"to_postal_code": "'.$to_postal_code.'",
	"weight": {
		"value": '.$weight.',
		"unit": "pound"
	}
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'API-Key: TEST_l7EknHfOt7KnT9PBVk4qxr8ZrOQxMNmGcO6D6gDgk2s'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$arr=json_decode($response);

 return response()->json(['status'=> true,'data' => $arr[0]], 200); 
    }


    public function noti()
    {
        try {
           
           
                // $deviceToken = $user->fcm_token;
                // $deviceToken = $user->fcm_token;
                $deviceToken = "doqIsZm3TmaZJomynokkEb:APA91bGf0HL2TOVu-zjt5V1m-KsqxKS44SfhYBUl9sBdUy3Dl6MVBiwoiBidYxEPkpjrv70H9Y7lDnZ8AzyIBBLeOWSH-fSifUAxpDNaQArHlkUuopqOLkqXBg3yK9WlfTUwkyefUwSl";

                if ($deviceToken) {
                    $response = $this->fcmService->sendNotification($deviceToken, 'Title133', 'This is message body33.');
                    $responses[] = $response;
                }
            
            return response()->json([
                'status' => 'true',
                'data' => $responses,
                'message' => 'Notifications sent successfully.',
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'false',
                'data' => $th->getMessage(),
                'message' => 'An error occurred. Please try again later.',
            ]);
        }
    }
}
