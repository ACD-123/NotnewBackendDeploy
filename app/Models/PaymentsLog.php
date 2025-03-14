<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\flexefee;

/**
 * App\Models\Prices
 * 
 * @property integer $id
 * @property string $payment_status
 * @property string $Curency
 * @property string $payment_intents
 * @property string $Customer
 * @property string $payment_method_type
 * @property float $Amount
 * @property datetime $created_at
 * @mixin \Eloquent
 */ 
class PaymentsLog extends Model
{
    public $table = "payments_log";
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    const PAYMENT_METHOD_TYPE = 'card';
    const STRIPE_FEE = 2.9;
    const FLEXE_FEE = 5;

    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = [
                    'payment_status',
                    'Curency', 
                    'payment_intents', 
                    'Customer', 
                    'payment_method_type', 
                    'Amount',
                    'payment_mode',
                    'stipe_fee',
                    'flexe_fee',
                    'meta_data'
                ];
    
    public static function request($paymentIntent, $status, $paymentmode, $metadata)
    {
        /**
         * $metadata is data of object created for saving other data in table 
         * like fees etc
         */
        // $flexeFee = flexefee::select('fee')->first();
        // $flexePoint= $flexeFee->fee;
        PaymentsLog::updateOrCreate(
            [
                'payment_status' => $status,
                'Curency' => $paymentIntent->currency,
                'payment_intents' => $paymentIntent->id,
                'Customer' => $paymentIntent->customer,
                'payment_method_type' => self::PAYMENT_METHOD_TYPE,
                'Amount' => $paymentIntent->amount/100,
                'payment_mode'=>$paymentmode,
                'stipe_fee'=>(self::STRIPE_FEE/100 * $paymentIntent->amount/100) + 0.30,
                //'flexe_fee'=>($flexePoint/100 * $paymentIntent->amount/100),
                'meta_data'=>$metadata
            ]
        );
    }
}
