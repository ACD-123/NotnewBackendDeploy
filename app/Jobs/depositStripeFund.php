<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use App\Models\Order;
use App\Models\Prices;
use App\Models\User;
use App\Models\Setting;
use App\Models\PaymentsLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\StripeClient;
use Illuminate\support\facades\DB;

class depositStripeFund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $stripe = new StripeClient(env('STRIPE_SK'));
        // $paymentIntent = $stripe->paymentIntents->retrieve('pi_3MX6cGDeMHtmU7Ib1s6II1Nb');
        // $stripe->paymentIntents->capture($order->payment_intent);
       
        // Artisan::call('capture:funds');
    }
    
}
