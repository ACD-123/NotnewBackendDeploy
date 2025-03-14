<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Prices;
use App\Models\User;
use App\Models\TrustedSeller;
use App\Models\Product;
use App\Models\PaymentsVendorLog;
use Carbon\Carbon;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;
use App\Notifications\DepositAccount;
use App\Traits\AppliedFees;

class CaptureVendorFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capture:vendorfunds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Captures funds for orders that are past the 30 days protection.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     parent::__construct();
    // }
    use AppliedFees;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $request="";
        $order="";
        
        return DB::transaction(function () use ($request, $order) {  
            // $stripe = new StripeClient(env('STRIPE_SK'));
         
            // $orders =Order::where('id', 415)->first();
            // $seller = User::where('id',6)->first();
            //     $product = Product::where('id', 128)->first();
            //     // Create a Transfer to a connected account (later):
            //     $transfer = $stripe->transfers->create([
            //         // 'amount' => (int)$remaining,//$product->getPrice() * 100,
            //         'amount' => (int)$orders->price,//$remaining,//$product->getPrice() * 100,
            //         'currency' => 'usd',
            //         'destination' => $seller->stripe_account_id,
            //         // 'transfer_group' => $order->price
            //     ]);
            //     return $transfer;
            //     die();
              
            $stripe = new StripeClient(env('STRIPE_SK'));
            $orders = Order::whereDate('created_at', '<=', Carbon::now()->subDays(30)->toDateTimeString())
               ->where('vendorstatus', 'Delivered')
            //    ->
            //    where('id','415')
                ->whereNotNull('payment_intent')
                ->each(function (Order $order) use ($stripe) {
                    $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent);
                    if($paymentIntent->status === 'requires_capture')
                    {
                            $stripe->paymentIntents->capture($order->payment_intent);
                            $order->status = Order::STATUS_PAID;
                            $order->update();
                    }
                        $seller = User::where('id',$order->seller_id)->first();
                        $trustedSeller = TrustedSeller::where('user_id',$order->seller_id)->first();
                        // $stripe->paymentIntents->capture($order->payment_intent);
                        $account = $stripe->accounts->retrieve(
                            $seller->stripe_account_id,
                            []
                          );
                          if($account->capabilities->card_payments== "inactive" || $account->capabilities->transfers== "inactive")
                          {
                            $seller->notify(new DepositAccount($order));

                          }else{
                            $value = $order->price;//$paymentIntent['amount_received'];
                          //Getting Seller Account
                            $seller = User::where('id',$order->seller_id)->first();
                            //Remaining after subtracting Stripe Fee => Total Amount
                            $totalAmount = $this->feePriceCalculator($order->price);
                            // Create a Transfer to a connected account (later):
                            $flexePoint = ($trustedSeller->percentage/100) * $totalAmount;
                           
                            $sellerAmount = $totalAmount - $flexePoint;
                            $transfer = $stripe->transfers->create([
                                // 'amount' => (int)$remaining,//$product->getPrice() * 100,
                                'amount' => (int)$sellerAmount * 100,//(int)$sellerAmount,//(int)$order->price * 100,//($order->price/$trustedSeller->percentage * 100),//$remaining,//$product->getPrice() * 100,
                                'currency' => 'usd',
                                'destination' => $seller->stripe_account_id,
                            ]);
                            $metadata = null;
                            //For Payment Log
                            PaymentsVendorLog::request($paymentIntent,Order::STATUS_PAID,'Paid To Stripe',$metadata);
                            $order->status = Order::STATUS_PAID;
                            $order->vendorstatus = "Delivered and Paid";
                            $order->update();
                          }
                });
        });
    }
}
