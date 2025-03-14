<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\ShippingDetail;
use App\Models\PaymentsLog;
use App\Models\Refund;
use App\Models\Offer;
use App\Jobs\depositStripeFund;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StripeController extends Controller
{
    protected $stripe;
    const INCOMPLETE_STATUS='Incomplete',
     COMPLETE_STATUS='Succeeded',
     FEATURED_ADD = 'Featured Add',
     HIRE_CAPTAIN = 'Hire Captain',
     BUYING="Buying";

    public function __construct()
    {
         $this->stripe = new StripeClient(env('STRIPE_SK'));       
    }

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function generate(Request $request)
    {
         $user = User::where('id',  $request->get('userId'))->first();
         $paymentIntent = "";
            $paymentIntent = $this->stripe->paymentIntents->create([
                // 'amount' => $product->getPrice() * 100,
                'amount' => $request->get('total') * 100,
                'currency' => 'usd',
                // 'capture_method' => 'manual',
                // 'transfer_group' => $price,
                // 'transfer_data' => [
                //     // 'amount' => $remaining,
                //     'destination' => $product->user->stripe_account_id,
                // ],
            ]);
            $metadata = null;
            // $paymentIntent = $this->stripe->paymentIntents->create([
            //     'amount' => $product->getPrice() * 100,
            //     'currency' => 'usd',
            //     // 'capture_method' => 'manual',
            //     // 'payment_method_options' => [
            //     //     'card' => [
            //     //       'capture_method' => 'manual',
            //     //     ],
            //     //   ],
            //     // 'transfer_data' => [
            //     //     'destination' => $product->user->stripe_account_id,
            //     // ],
            // ]);
                // $product=Product::where('guid','=', $productGuid)->first();
                
            $paymentslog = PaymentsLog::request($paymentIntent,self::INCOMPLETE_STATUS,self::BUYING,$metadata);
            return ['client_secret' => $paymentIntent->client_secret];
    }

    public function checkAccount($account)
    {
       return $this->stripe->accounts->retrieve(
        $account,
        []
      );
    }
    /**
     * @param Request $request
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function feature(Request $request)
    {
        $metadata = null;
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => Product::getFeaturedPrice($request->get('choice')) * 100,
            'currency' => 'usd'
        ]);
        $paymentslog = PaymentsLog::request($paymentIntent,self::COMPLETE_STATUS,self::FEATURED_ADD,$metadata);
        return ['client_secret' => $paymentIntent->client_secret];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function hire(Request $request)
    {
        $metadata = null;
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => Product::getHirePrice($request->get('choice')) * 100,
            'currency' => 'usd'
        ]);
        $paymentslog = PaymentsLog::request($paymentIntent,self::COMPLETE_STATUS,self::HIRE_CAPTAIN,$metadata);
        return ['client_secret' => $paymentIntent->client_secret];
    }
    /**
     * @param Request $request
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getTransactions(Request $request)
    {
        $buyerstripe = Order::where(function ($query) {
            $query
            // ->where('buyer_id', '=', \Auth::user()->id)
            ->where('seller_id', '=', \Auth::user()->id);
                //   ->orWhere('seller_id', '=', \Auth::user()->id);
        })
        // ->where('deliver_status', 'pending')
        // ->Where('status', '!=', 'UNPAID')
        // ->where('deliver_status', '<>', 'completed')
        ->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        }, "buyer" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, "seller" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, 'shippingDetail' => function (BelongsTo $hasMany) {
            $hasMany->select(ShippingDetail::defaultSelect());
        }, 'refund' => function ($query) {
            $query->select(Refund::defaultSelect());
        }, 'offer' =>function($query){
            $query->select(Offer::defaultSelect());
        }])->get();
        return $buyerstripe;
       
    }
    public function getPaymentsStatus(Request $request){

        $orders = Order::where(function ($query) {
            $query->where('buyer_id', '=', \Auth::user()->id)
                  ->orWhere('seller_id', '=', \Auth::user()->id);
        })
        //->Where('status', '!=', 'UNPAID')
        ->Where('deliver_status', 'pending')
        ->where('payment_intent','!=', null)->get();
        $status = [];
        foreach($orders as $order){
            array_push($status,$order->id);
        }
        // $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));
        // $stripeReturns = [];
        // foreach($status as $stat){
        //    array_push($stripeReturns,$stripe->paymentIntents->retrieve(
        //         $stat,
        //         []
        //     ));
        // }
       
        return $status;
    }
    public function updateUserAccount(Request $request){

        $bankaccount = $this->stripe->accountLinks->create(
        [
          'account' => $request[0],
          'refresh_url' => env('STRIPE_REFRESH_URL'),
          'return_url' => env('STRIPE_RETURN_URL'),
          'type' => 'account_onboarding',
        ]
      );
      return $bankaccount;
   
    }
    public function addUserAccforPostAdd(Request $request, $uuid){
     
        $bankaccount = $this->stripe->accountLinks->create(
        [
          'account' => $request[0],
          'refresh_url' => env('STRIPE_REFRESH_URL'),
          'return_url' => env('STRIPE_RETURN_URL_POST_ADD').$uuid,
          'type' => 'account_onboarding',
        ]
      );
      return $bankaccount;
   
    }
    
    /**
     * @param Request $request
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function balance(Request $request)
    {
        $user = User::where('id','=', \Auth::user()->id)->first();
        
        $orders = Order::where('seller_id', '=', \Auth::user()->id)
        ->Where('status', '!=', 'UNPAID')->get();
        $stripe = new \Stripe\StripeClient(env('STRIPE_SK'));
        $balance = $stripe->balance->retrieve([], ['stripe_account' => $user->stripe_account_id]);
        $availableBalance = $balance->available;
        $availBal =[];
        foreach($availableBalance as $available){
            array_push($availBal, $available->amount);
        }
        return array_sum($availBal)/100;

        // $status = [];
        // foreach($orders as $order){
        //     array_push($status,$order->payment_intent);
        // }
        
        
        // $stripeReturns = [];
        // foreach($status as $stat){
        //    array_push($stripeReturns,$stripe->paymentIntents->retrieve(
        //         $stat,
        //         []
        //     ));
        // }
        // $amount = [];
        // foreach($stripeReturns as $shipretun){
        //     if($shipretun->amount_capturable != "0"){
        //         // array_push($amount, $shipretun->amount/100);
        //         array_push($amount, $shipretun->amount_capturable/100);
        //     // }else{
        //     //     array_push($amount, 0/100);
        //     }
        // }
        // return $amount;
        // die();
        // return array_sum($amount);
    }

    public function getBankAccounts(Request $request){
        $externalAccounts = $this->stripe->accounts->allExternalAccounts(
            $request[0],
            ['object' => 'bank_account', 'limit' => 1]
          );
        return $externalAccounts;
    }

    public function getPaymentIntents(Request $request, $id){
        $orders = Order::where('seller_id', $id)
        // where('buyer_id', $id)
        //             ->orWhere('seller_id', $id)
                    ->get();
        $paymentIntents = [];
        foreach ($orders as $index => $order) {
            if($order->payment_intent){
                array_push($paymentIntents, $order->payment_intent);
            }
        }
        $intentAmounts = [];
        foreach ($paymentIntents as $index => $paymentIntent) {
            $getPaymentIntents =$this->stripe->paymentIntents->retrieve(
                $paymentIntent,
                []
            );
            if($getPaymentIntents->status == "requires_capture"){
                array_push($intentAmounts,$getPaymentIntents->amount);
            }
        }
        $intentAmounts = array_sum($intentAmounts);
        $totalAmount = $intentAmounts/100;
        return $totalAmount;
        // $getPaymentIntents = $this->stripe->paymentIntents->retrieve(
        //     'pi_3LD6IeDeMHtmU7Ib2DIzKdRF',
        //     []
        //   );
        // return $getPaymentIntents;
    }

}
