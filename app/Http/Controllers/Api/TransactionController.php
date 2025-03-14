<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\Fedex;
use App\Models\EasyPost;
use App\Models\USPS;
use App\Models\Order;
use App\Models\UserCart;
use App\Models\UserOrder;
use App\Models\Prices;
use App\Models\Product;
use App\Models\flexefee;
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


class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
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

    public function getUserTransactions(Request $request){
        $transactions =  Transaction::where('user_id',\Auth::user()->id)->get();
        $amounts = [];
        foreach($transactions as $transaction){
            array_push($amounts,$transaction->amount);
        }
        return array_sum($amounts);
    }

    public function getTransactions(Request $request){
        $transactions =  Transaction::where('user_id',\Auth::user()->id)
        ->with(['bank'])
        ->with(['user'])
        ->get();
        return $transactions;
    }
    public function getStripeTransactions(Request $request){
        $transactions =  Transaction::where('user_id',\Auth::user()->id)
        ->where('transaction_type', 'Stripe')
        ->with(['bank'])
        ->with(['user'])
        ->get();
        return $transactions;
    }
    

    public function store(Request $request)
    {    
        //
    }
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    { 
        //
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
     * @return Transaction
     */
        public function update(Order $order, Request $request)
        {
            //
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

}
