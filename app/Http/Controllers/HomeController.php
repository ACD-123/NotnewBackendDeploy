<?php

namespace App\Http\Controllers;
use App\Models\UserOrder;
use App\Models\UserOrderDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;

use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $orderCountPending = UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'pending');
        })
        ->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'pending');
        })->count();
        $orderCountRefund = UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'COMPLETED')->where('refunded',1);
        })
        ->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'COMPLETED');
        })->count();
        $orderCountRejected =UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'rejected');
        })
        ->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'rejected');
        })->count();
        $orderCountAccepted =UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'accepted');
        })
        ->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'accepted');
        })->count();
        $orderCountComplete = UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'COMPLETED')->where('refunded',0);
        })
        ->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'COMPLETED');
        })->count();
        $totalPrice = UserOrder::whereHas('orderDetails', function ($query) {
            $query->where('status', '=', 'COMPLETED')->where('refunded',0);
        })->whereDoesntHave('orderDetails', function ($query) {
            $query->where('status', '!=', 'COMPLETED');
        })->sum('shipping_cost');
        $order = UserOrder::with(['buyer', 'orderDetails.product'])
        ->when($request->search, function($query) use ($request) {
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
        ->orderBy('created_at', 'DESC')
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
            // return $order;
       
        return view('pages.dashboard', ['pending' => $orderCountPending, 'refund' => $orderCountRefund, 'rejected' => $orderCountRejected, 'accepted' => $orderCountAccepted, 'complete' => $orderCountComplete, 'totalPrice' => $totalPrice, 'order' => $order,'dates'=>$dates,'orderTotals'=>$orderTotals,'result'=>$result]);
    }
}
