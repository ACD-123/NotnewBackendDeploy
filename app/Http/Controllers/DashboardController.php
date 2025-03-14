<?php

namespace App\Http\Controllers;

use App\Models\FeedBack;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerData;
use App\Models\User;
use App\Models\ReportSeller;
use App\Models\UserStoreVouchers;
use App\Models\SellerTransaction;
use App\Models\UserOrder;
use App\Models\Coupon;
use App\Models\Help;
use Illuminate\Http\Request;
use App\Helpers\StripeHelper;
use App\Models\UserCart;
use App\Models\UserOrderDetails;
use Carbon\Carbon;
use DB;
use App\Models\UserBank;
use App\Helpers\GuidHelper;



class DashboardController extends Controller
{
    public function sendPromotionalNotification(Request $request)
    {
        if ($request->hasFile('image')) {
            $originalName = str_replace(' ', '_', $request->image->getClientOriginalName());
            $mainimage = time() . '_' . $originalName;                
            $request->image->move(public_path('image/category/'), $mainimage);
            $imagePath = $mainimage;
        }
        $users=User::where('status',1)->where('is_admin',0)->get();
        foreach ($users as $user) {
            $auctioned=0;
            if($request->type=="product"){
               $product=Product::where('guid',$request->guid)->first();
               $auctioned=$product->auctioned;
            }
            $notificationArray=[
                "title"=>$request->title,
                "message"=>$request->message,
                "type"=>"important",
                "user_id"=>$user->id,
                "sender_id"=>auth()->id(),
                "notification_type"=>"important",
                "url"=>$request->url,
                "notificationtype"=>$request->type,
                "guid"=>$request->guid,
                "auctioned"=>$auctioned
            ];
            if ($request->hasFile('image')) {
                $notificationArray['image'] = env("APP_URL")."public/image/category/".$imagePath;
            }
            StripeHelper::saveNotification($notificationArray);

        }
        return redirect()->back()
        ->with('success', 'Notification Sent');
    }

    public function dashboard()
    {

        $orderCountPending = UserOrder::where('status', 'pending')->count();
        $orderCountRefund = UserOrder::where('status', 'refund')->count();
        $orderCountRejected = UserOrder::where('status', 'rejected')->count();
        $orderCountAccepted = UserOrder::where('status', 'accepted')->count();
        $orderCountComplete = UserOrder::where('status', 'COMPLETED')->count();
        $totalPrice = UserOrder::where('status', 'COMPLETED')->sum('shipping_cost');
        $order = UserOrder::with(['buyer', 'orderDetails.product'])
            ->whereDate('created_at', now()->format('Y-m-d')) // Use 'now()' to get current date
            ->get();


        return view('pages.dashboard', ['pending' => $orderCountPending, 'refund' => $orderCountRefund, 'rejected' => $orderCountRejected, 'accepted' => $orderCountAccepted, 'complete' => $orderCountComplete, 'totalPrice' => $totalPrice, 'order' => $order]);
    }

    public function userManagement()
    {
        // return GuidHelper::getGuid();
        // die;
        $checkUser =  SellerData::pluck('user_id')->toArray();
        // dd($checkUser);
        $vendors = User::where('is_admin',0)->get();
        $customer = User::where('is_admin',0)->get();
        $filteredVendors = [];

        foreach ($vendors as $vendor) {
            $checkVendor = SellerData::where('user_id', $vendor->id)->first();

            if ($checkVendor) {
                $feedback = FeedBack::where('store_id', $checkVendor->id)->get();
                $feedbackCount = FeedBack::where('store_id', $checkVendor->id)->count();

                $filteredVendors[] = [
                    'vendor' => $vendor,
                    'shop' => $checkVendor,
                    'feedback' => $feedback,
                    'feedbackCount' => $feedbackCount
                ];
            }
        }
        $currentYear = Carbon::now()->year;
                $shippingCosts = User::select(
                     DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereYear('created_at', $currentYear)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month');
                $result = [];
                for ($month = 1; $month <= 12; $month++) {
                    $result[] = $shippingCosts->get($month, 0);
                }
                // return ['data' => $filteredVendors, 'customer' => $customer,'result'=>$result];
        return view('pages.userManagement', ['data' => $filteredVendors, 'customer' => $customer,'result'=>$result]);
    }

    public function customer()
    {
        $customer = User::all(); // Get all users


        // Pass the filtered data to the view
        return view('pages.userManagement', []);
    }

    public function activeInactiveVendor($id)
    {
        $vendors = User::where('id', $id)->first();

        if ($vendors->isTrustedSeller == 1) {
            $vendors->update(['isTrustedSeller' => 0]);
        } elseif ($vendors->isTrustedSeller == 0) {
            $vendors->update(['isTrustedSeller' => 1]);
        }
        return redirect(route('userManagement'));
    }

    public function orderManagement(Request $request)
    {
        try {
            $orderCount=UserOrder::count();
            $order = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])->when($request->search, function($query) use ($request) {
                $query->where('orderid', 'like', '%' . $request->search . '%');
            })
            ->when($request->date, function($query) use ($request) {
                $query->where('created_at', 'like', '%' . $request->date . '%');
            })
            ->when($request->status, function($query) use ($request) {
                $query->whereHas('orderDetails', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            })
            ->orderBy('created_at', 'DESC')->get();
            $orderOngoing = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'pending');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'pending');
                })
                ->get();
            $orderComplete = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'COMPLETED');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'COMPLETED');
                })
                ->get();

            $orderAccepted = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'accepted');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'accepted');
                })
                ->get();
            $orderRrefund = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'COMPLETED')->where('refunded',1);
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'COMPLETED');
                })
                ->get();
            $orderRejected = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'rejected');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'rejected');
                })
                ->get();
                $dates = [];
                $orderTotals = [];
                $today = Carbon::now();
                for ($i = 0; $i < 7; $i++) {
                    $date = $today->copy()->subDays($i)->format('Y-m-d');
                    $dates[] = $today->copy()->subDays($i)->format('M j');
                    $ordersNew=UserOrder::with('orderDetails')->whereDate('created_at', $date)->get();
                    $total=0;
                    foreach ($ordersNew as $orderNew) {
                        $total+=$orderNew->shipping_cost;
                        foreach($orderNew->orderDetails as $detail)
                        {
                            $total+=$detail->price;
                        }

                    }
                    $orderTotals[]=round($total,2);
                }
                $dates = array_reverse($dates);
                $orderTotals=array_reverse($orderTotals);
                if ($orderCount > 0) {
                    $completedPercentage=round((count($orderComplete)/$orderCount)*100,2);
                    $refundPercentage=round((count($orderRrefund)/$orderCount)*100,2);
                    $remainingTotal=count($orderOngoing)+count($orderAccepted)+ count($orderRejected);
                    $ongoingPercentage=round(($remainingTotal/$orderCount)*100,2);
                 
    
                } else {
                    $completedPercentage = 0;
                    $refundPercentage = 0;
                    $ongoingPercentage = 0;
                }
                

            return view('pages.orderManagement', ['order' => $order, 'ongonig' => $orderOngoing, 'complete' => $orderComplete, 'accepted' => $orderAccepted, 'refund' => $orderRrefund, 'rejected' => $orderRejected,'dates'=>$dates,'orderTotals'=>$orderTotals,'orderCount'=>$orderCount,'completedPercentage'=>$completedPercentage,'refundPercentage'=>$refundPercentage,'ongoingPercentage'=>$ongoingPercentage]); 
        } catch (\Throwable $th) {
            \Log::error('Error fetching orders: ' . $th->getMessage()); // Log the error
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function bids()
    {
        $ongoing=Product::with('media')->where('auctioned',1)->where('is_sold',0)->where('is_deleted',0)->get();
        foreach ($ongoing as $value) {
            $value->final_bid=$value->max_bid;
            $value->auction_type=1;
        }
        $complete=Product::with('media')->where('auctioned',1)->where('is_sold',1)->where('is_deleted',0)->get();
        foreach ($complete as $value) {
            $value->auction_type=0;
            $cart=UserCart::where('product_id',$value->id)->first();
            if($cart)
            {
                $value->max_user=User::where('id',$cart->user_id)->first();
                $value->final_bid=$cart->price;
            }
            else
            {
                $orderDetail=UserOrderDetails::with('order')->where('product_id',$value->id)->first();
                if($orderDetail){
                    $value->max_user=User::where('id',$orderDetail->order->buyer_id)->first();
                    $value->final_bid=$orderDetail->price;
                }
                else{
                    $value->final_bid=$value->max_bid;
                }
            }
        }
        $all = $ongoing->merge($complete);
        return view('pages.bidsOffers',["ongoing"=>$ongoing,"complete"=>$complete,"all"=>$all]);
    }
    public function orderDetail($id)
    {
        try {
            $subTotal = 0;
            $shippingcost = 0;
            $voucherDiscount = 0;
            $ordertotal = 0;
            $orderDetail = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.store'])->where('id', $id)->first();
            $voucherDiscount=0;
            $vouchers=UserStoreVouchers::where('order_id',$id)->get();
            foreach ($vouchers as $voucher) {
                if($voucher){
                    $counpon=Coupon::where('code',$voucher->coupon_code)->first();
                    $voucherDiscount+=$counpon->discount??0;
                  }
            }
            $subTotal = $orderDetail->order_total;
            $shippingcost = $orderDetail->shipping_cost;
            $voucherDiscount = $voucherDiscount;
            $ordertotal = $orderDetail->order_total+$orderDetail->shipping_cost-$voucherDiscount;
            
            return view('pages.order.orderDetail', ['orderDetail' => $orderDetail , 'shippingcost'=>$shippingcost , 'voucherDiscount'=>$voucherDiscount , 'ordertotal'=>$ordertotal , 'subtotal' => $subTotal] );
        } catch (\Throwable $th) {
            \Log::error('Error fetching orders: ' . $th->getMessage());
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    

    public function vendorDashboard(Request $request , $id)
    {
        try {
            $total = 0;
            $completedPercentage = 0;
            $refundPercentage = 0;
            $remainingTotal = 0;
            $ongoingPercentage = 0;


            

            $checkVendor = SellerData::where('user_id',$id)->pluck('id')->toArray();
            // return $checkVendor;
            // $orderCount=UserOrder::where('buyer_id', $id)->count();
            $orderCount = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor);
            })->count();

           

            $ongoing = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor)->where('status', 'pending');
            })->count();
            $refund = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor)->where('status', 'COMPLETED')->where('refunded',1);
            })->count();
            $complete = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor)->where('status', 'COMPLETED')->where('refunded',0);
            })->count();
            $accept = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor)->where('status', 'accepted');
            })->count();
            $rejected = UserOrder::with('orderDetails.product')
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor)->where('status', 'rejected');
            })->count();
            $vendorShop = SellerData::where('user_id', $id)->first();


            

             $vendorOrder = UserOrder::with(['orderDetails.product'])->when($request->search, function($query) use ($request) {
                $query->where('orderid', 'like', '%' . $request->search . '%');
            })
            ->when($request->date, function($query) use ($request) {
                $query->where('created_at', 'like', '%' . $request->date . '%');
            })
            ->when($request->status, function($query) use ($request) {
                $query->whereHas('orderDetails', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            })
            ->whereHas('orderDetails', function($qury) use($checkVendor) {
                $qury->whereIn('store_id', $checkVendor);
            })
            ->orderBy('created_at', 'DESC')->paginate(16);
            foreach ($vendorOrder as $vendorOrders) {
                foreach ($vendorOrders->orderDetails as $products) {
                    if($products->sale_price == 0 || !$products->sale_price == null){
                        $total += $products->product->sale_price  * $products->quantity;
                    }else{
                        $total += $products->product->price * $products->quantity;
                    }
                
                }
            }
            $chart = UserOrder::with(['orderDetails.product'])
    ->whereHas('orderDetails', function($query) use($checkVendor) {
        $query->whereIn('store_id', $checkVendor);
    })
    ->join('tbl_user_order_details', 'tbl_user_order.id', '=', 'tbl_user_order_details.order_id') // Join orderDetails
    ->join('products', 'tbl_user_order_details.product_id', '=', 'products.id') // Join products table
    ->join('seller_datas', 'tbl_user_order_details.store_id', '=', 'seller_datas.id') // Join products table
    ->select(
        DB::raw('YEAR(tbl_user_order.created_at) as year'),
        DB::raw('MONTH(tbl_user_order.created_at) as month'),
        DB::raw('MONTHNAME(tbl_user_order.created_at) as month_name'),
        DB::raw('COUNT(*) as total_orders'),
        DB::raw('SUM(CASE 
                    WHEN products.sale_price = 0 OR products.sale_price IS NULL THEN products.price * tbl_user_order_details.quantity
                    ELSE products.sale_price * tbl_user_order_details.quantity
                  END) as order_total')
    )
   
    ->groupBy(DB::raw('YEAR(tbl_user_order.created_at)'), DB::raw('MONTH(tbl_user_order.created_at)'), DB::raw('MONTHNAME(tbl_user_order.created_at)'))
    ->orderBy('year')
    ->orderBy('month')
    ->get();

            if ($orderCount > 0) {
                $completedPercentage = ($complete / $orderCount) * 100;
                $refundPercentage = ($refund / $orderCount) * 100;
                $remainingTotal = ($ongoing + $accept) + $rejected;
                $ongoingPercentage = ($remainingTotal / $orderCount) * 100;
             

            } else {
                $completedPercentage = 0;
                $refundPercentage = 0;
                $ongoingPercentage = 0;
            }
            $vendorDashboard[] = [
                'totalOrder' => $orderCount,
                'total' => $total,
                'ongoing' => $ongoing,
                'refund' => $refund,
                'complete' => $complete,
                'completedPercentage'=>(int)$completedPercentage,
                'refundPercentage'=>(int)$refundPercentage,
                'ongoingPercentage'=>(int)$ongoingPercentage,
                'shop' => $vendorShop,
                'chart' => $chart,
                'vendorOrder' => $vendorOrder,
                

            ];
            

            // return $vendorDashboard;
            return view('pages.vendor.vendorDashboard', ['vendorDashboard' => $vendorDashboard,'id'=>$id]);
        } catch (\Throwable $th) {
            \Log::error('Error fetching orders: ' . $th->getMessage());
            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function report()
    {
        $dates = [];
        $orderTotals = [];
        $today = Carbon::now();
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');
            $dates[] = $today->copy()->subDays($i)->format('M j');
            $ordersNew=UserOrder::with('orderDetails')->whereDate('created_at', $date)->get();
            $total=0;
            foreach ($ordersNew as $orderNew) {
                $total+=$orderNew->shipping_cost;
                foreach($orderNew->orderDetails as $detail)
                {
                    $total+=$detail->price;
                }

            }
            $orderTotals[]=round($total,2);
        }
        $dates = array_reverse($dates);
        $orderTotals=array_reverse($orderTotals);
        $orderCount=UserOrder::count();
        $orderOngoing = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'pending');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'pending');
                })
                ->count();
            $orderComplete = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'COMPLETED');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'COMPLETED');
                })
                ->count();

            $orderAccepted = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'accepted');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'accepted');
                })
                ->count();
            $orderRrefund = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'COMPLETED')->where('refunded',1);
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'COMPLETED');
                })
                ->count();
            $orderRejected = UserOrder::with(['buyer', 'orderDetails.product','orderDetails.product.media'])
                ->whereHas('orderDetails', function ($query) {
                    $query->where('status', '=', 'rejected');
                })
                ->whereDoesntHave('orderDetails', function ($query) {
                    $query->where('status', '!=', 'rejected');
                })
                ->count();
                if ($orderCount > 0) {
                    $completedPercentage=round(($orderComplete/$orderCount)*100,2);
                    $refundPercentage=round(($orderRrefund/$orderCount)*100,2);
                    $remainingTotal=$orderOngoing+$orderAccepted+ $orderRejected;
                    $ongoingPercentage=round(($remainingTotal/$orderCount)*100,2);
                 
    
                } else {
                    $completedPercentage = 0;
                    $refundPercentage = 0;
                    $ongoingPercentage = 0;
                }
                
                $currentYear = Carbon::now()->year;
                $shippingCosts = UserOrder::select(
                     DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(shipping_cost) as total_shipping_cost')
                )
                ->whereYear('created_at', $currentYear)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total_shipping_cost', 'month');
                $orderDetailsPrices = UserOrderDetails::select(
                    DB::raw('MONTH(tbl_user_order.created_at) as month'),
                    DB::raw('SUM(tbl_user_order_details.price) as total_price')
                )
                ->join('tbl_user_order', 'tbl_user_order_details.order_id', '=', 'tbl_user_order.id') // Adjust the join condition if needed
                ->whereYear('tbl_user_order.created_at', $currentYear)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total_price', 'month');
                $result = [];
                for ($month = 1; $month <= 12; $month++) {
                    $result[] = round($shippingCosts->get($month, 0)+$orderDetailsPrices->get($month, 0),2);
                }
            

        return view('pages.reportAnalytic',compact('dates','orderTotals','orderCount','completedPercentage','refundPercentage','ongoingPercentage','result'));
    }

    public function reportAdmin()
    {

        $report = ReportSeller::with(['user','seller'])->orderBy('created_at', 'DESC')->paginate(16);
        return view('pages.report.index', ['report' => $report]);

    }
    public function helpAndSupport()
    {

        $help = Help::with(['user'])->orderBy('created_at', 'DESC')->paginate(16);
        return view('pages.helpAndSupport.index', ['help' => $help]);

    }

    public function sellerWithdraw()
    {

        $withdraw = SellerTransaction::with(['seller'])->where('type','Withdraw')->orderBy('created_at', 'DESC')->paginate(10);
        foreach ($withdraw as $value) {
          
            $value->user_bank=UserBank::with('bank')->where('user_id',$value->seller->user_id)->first();
           
        }
        return view('pages.sellerWithdraw.index', ['withdraw' => $withdraw]);

    }
    public function sellerWithdrawStatus(Request $request)
    {
        if ($request->hasFile('image')) {
            $originalName = str_replace(' ', '_', $request->image->getClientOriginalName());
            $mainimage = time() . '_' . $originalName;                
            $request->image->move(public_path('image/category/'), $mainimage);
            $imagePath = $mainimage;
        }
        $id = $request->service_id;
        $sellerTransaction = SellerTransaction::with('seller')->find($id);
        if (!$sellerTransaction) {
            return redirect()->back()->with('error', 'Seller transaction not found.');
        }
        $status = $request->status;
        $amountWithdraw = $request->amount_withdraw;
        if ($status == "rejected") {
            $notes = $request->notes;
            $sellerTransaction->update([
                "status"=>"Rejected",
                "note"=>$notes
            ]);
            $notificationArray=[
                "title"=>"Withdraw Transaction Rejected By Admin!",
                "message"=>$notes,
                "type"=>"important",
                "user_id"=>$sellerTransaction->seller->user_id,
                "sender_id"=>auth()->id(),
                "notification_type"=>"important",
                "url"=>"https://notnew.testingwebsitelink.com/my-seller-account?tab=m-transactions",
                "notificationtype"=>"seller",
                "guid"=>$sellerTransaction->seller->guid,
            ];
            StripeHelper::saveNotification($notificationArray);
            return redirect()->back()->with('success', 'Transaction Updated!');
        }
        if ($status == "approved") {
            if ($sellerTransaction->amount == $amountWithdraw) {
                $notificationArray=[
                    "title"=>"Withdraw Transaction Approved By Admin!",
                    "message"=>"Withdraw Transaction Of $".$amountWithdraw." Approved By Admin!",
                    "type"=>"important",
                    "user_id"=>$sellerTransaction->seller->user_id,
                    "sender_id"=>auth()->id(),
                    "notification_type"=>"important",
                    "url"=>"https://notnew.testingwebsitelink.com/my-seller-account?tab=m-transactions",
                    "notificationtype"=>"seller",
                    "guid"=>$sellerTransaction->seller->guid,
                ];
                if ($request->hasFile('image')) {
                    $notificationArray['image'] = env("APP_URL")."public/image/category/".$imagePath;
                }
                StripeHelper::saveNotification($notificationArray);
                $sellerTransaction->update([
                    "status"=>"Approved",
                    "image"=>env("APP_URL")."public/image/category/".$imagePath
                ]);
                return redirect()->back()->with('success', 'Transaction Updated!');
            }
            else{
                $remainingAmount = $sellerTransaction->amount - $amountWithdraw;
                $sellerTransaction->update([
                    "amount"=>$remainingAmount
                ]);
                SellerTransaction::create([
                    "order_id"=>0,
                    "amount"=>$amountWithdraw,
                    "type"=>"Withdraw",
                    "date"=>date("Y-m-d H:i:s"),
                    "message"=>"Your Withdraw of $".$amountWithdraw." is approved from Admin!",
                    "seller_guid"=>$sellerTransaction->seller_guid,
                    "status"=>"Approved",
                    "image"=>env("APP_URL")."public/image/category/".$imagePath
                ]);
                $notificationArray=[
                    "title"=>"Withdraw Transaction Approved By Admin!",
                    "message"=>"Withdraw Transaction Of $".$amountWithdraw." Approved By Admin!",
                    "type"=>"important",
                    "user_id"=>$sellerTransaction->seller->user_id,
                    "sender_id"=>auth()->id(),
                    "notification_type"=>"important",
                    "url"=>"https://notnew.testingwebsitelink.com/my-seller-account?tab=m-transactions",
                    "notificationtype"=>"seller",
                    "guid"=>$sellerTransaction->seller->guid,
                ];
                if ($request->hasFile('image')) {
                    $notificationArray['image'] = env("APP_URL")."public/image/category/".$imagePath;
                }
                StripeHelper::saveNotification($notificationArray);
                return redirect()->back()->with('success', 'Transaction Updated!');
            }
        }
    }
}
