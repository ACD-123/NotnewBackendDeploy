<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AppliedFees {
    /**
     * @param Request $request
     * @return $this|false|string
     */
    public function feePriceCalculator($amount) {
        
        
        $stripeFixedFee = 2.9;
        $stripeFixedPriceCents = 0.3;
        $StripeFixedPercValue = (($stripeFixedFee / 100)* $amount) + $stripeFixedPriceCents;
        $stripeDeductionFees	= ($amount - $StripeFixedPercValue);
       
        return $stripeDeductionFees;

    }

}