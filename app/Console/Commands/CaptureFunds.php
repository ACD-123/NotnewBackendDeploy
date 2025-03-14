<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Prices;
use App\Models\User;
use App\Models\Product;
use App\Models\EasyPost;
use App\Models\TrustedSeller;
use App\Models\PaymentsLog;
use App\Models\flexefee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;
use App\Traits\AppliedFees;

class CaptureFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capture:funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Captures funds for orders that are past the 2 days protection.';

    use AppliedFees; 
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::channel('cronjobs')->info('testing capture funds');
       
        $request="";
        $order="";
        return DB::transaction(function () use ($request, $order) {  
            $stripe = new StripeClient(env('STRIPE_SK'));
            //for Updating Order Status Delivered by EasyPost
            // $orders_delivered = Order::whereDate('created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
            $orders_delivered = Order::where('status', Order::STATUS_UNPAID)
            //    ->where('id','231')
                ->whereNotNull('payment_intent')
                ->where('deliver_status','!=' ,Order::DELIVERED)
                ->where('deliver_status','!=' ,Order::COMPLETED)
                ->each(function (Order $order) use ($stripe) {
                    $trustedSeller = TrustedSeller::where('user_id',$order->seller_id)->first();
                    if($trustedSeller){
                        //
                    }else{
                        $data = "";
                        $trackingId = $order->tracking_id;
                        $trackerShipment = EasyPost::trackShipment($trackingId);
                        $trackerShipment = json_decode($trackerShipment);
                        if($trackerShipment)
                        {
                            if($trackerShipment->status == Order::DELIVERED){
                                $order->deliver_status = Order::DELIVERED;
                                $order->delivered_at = Carbon::now()->toDateTimeString();
                                $order->update();
                            };    
    
                        }
                    } 
                });
                // dd($stripe->balance->retrieve([]));
            //For Payments
            $orders = Order::whereDate('delivered_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
               ->where('status', Order::STATUS_UNPAID)
               ->where('deliver_status',Order::DELIVERED)
                ->whereNotNull('payment_intent')
                ->each(function (Order $order) use ($stripe) {
                $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent);
                    // if($paymentIntent->status === 'requires_capture')
                    // {
                        $trustedSeller = TrustedSeller::where('user_id',$order->seller_id)->first();
                        if($trustedSeller){
                            // $stripe->paymentIntents->capture($order->payment_intent);
                            $order->status = Order::STATUS_PAID;
                            $order->update();
                        }else{
                            
                            // $stripe->paymentIntents->capture($order->payment_intent);
                            //$value = \Session::get('transferGroup');
                            // $charge = $stripe->charges->create(array(
                            //     'currency' => 'USD',
                            //     'amount'   => $paymentIntent->amount,
                            //     'source' => 'tok_bypassPending'
                            // ));
                            $value = $order->actual_price;//$paymentIntent['amount_received'];
                            //Remaining after subtracting Stripe Fee => Total Amount
                            //$totalAmount = $this->feePriceCalculator($order->price);
                            
                            // $remaining = $value - $feePriceCalculator;
                            $flexeFee = flexefee::select('fee')->first();
                            $flexePoint= $flexeFee->fee;
                            
                            $flexeAmount = ($flexePoint/100) * $value;
                            //$flexeAmount = $order->shipping_rates + $flexeAmount;
                            $sellerAmount = $value - $flexeAmount;

                            //Truncate Number like 2.84 not 2.8452
                            $sellerAmount = $this->truncate_number($sellerAmount);
                            
                            $sellerAmount = $sellerAmount * 100;
                            //Getting Seller Account
                            $seller = User::where('id',$order->seller_id)->first();
                            $product = Product::where('id', $order->product_id)->first();
                            // dd((int)$sellerAmount * 100);
                            // Create a Transfer to a connected account (later):
                            $transfer = $stripe->transfers->create([
                                // 'amount' => (int)$remaining,//$product->getPrice() * 100,
                                'amount' => $sellerAmount,//(int)$sellerAmount * 100,//$product->getPrice() * 100,
                                'currency' => 'usd',
                                'destination' => $seller->stripe_account_id,
                                'transfer_group' => $order->price
                            ]);
                            
                            // Create a second Transfer to another connected account (later):
                           
                            // $transfer = $stripe->transfers->create([
                            //     // 'amount' => (int)$percent,
                            //     'amount' => (int)$flexeAmount * 100,
                            //     'currency' => 'usd',
                            //     // Flexe Admin Stripe Account => acct_1MH75gReJCB3JLnc
                            //     'destination' => 'acct_1MH75gReJCB3JLnc', 
                            //     'transfer_group' => $order->price
                            // ]);
                            $metadata = null;
                            //For Payment Log
                            PaymentsLog::request($paymentIntent,Order::STATUS_PAID,'Paid To Stripe',$metadata);
                            $order->status = Order::STATUS_PAID;
                            $order->deliver_status = Order::COMPLETED;
                            $order->update();
                            //4000000000000077
                           
                        }
                // }else{
                        
                //     /**                         * 
                //      * if Captures in stripe but not Updated 
                //      * in Orders so it would be PAID 
                //      */
                //     $metadata = null;
                //     $paymentIntent = $stripe->paymentIntents->retrieve($order->payment_intent);
                //     PaymentsLog::request($paymentIntent,Order::STATUS_PAID,'Paid To Stripe',$metadata);
                //     $order->status = Order::STATUS_PAID;
                //     $order->update();
                // }
            });
        });
    }
    function truncate_number($number, $precision = 2) {

        // Zero causes issues, and no need to truncate
        if (0 == (int)$number) {
            return $number;
        }
    
        // Are we negative?
        $negative = $number / abs($number);
    
        // Cast the number to a positive to solve rounding
        $number = abs($number);
    
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
    
        // Run the math, re-applying the negative value to ensure
        // returns correctly negative / positive
        return floor( $number * $precision ) / $precision * $negative;
    }

    public function stripePriceCalculator($val){
        
        /**
         * 2.9% + 30¢ for Regular Stripe Payment
         */
        $stripefee = env('STRIPE_FEE');//Prices::where('name', 'stripe fee')->first();
      
        $val = $stripefee/100 * $val;
        /**
         * 0.029 + 0.30; 30 Cents are added
         * according to stripe documentations
         */
        $val = $val + 0.30;
        
        return $val;
    }
}
