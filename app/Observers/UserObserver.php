<?php

namespace App\Observers;

use App\Helpers\StringHelper;
use App\Models\User;

use Stripe\StripeClient;

class UserObserver
{
    /**
     * @param User $user
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function creating(User $user)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        // $account = $stripe->accounts->create([
        //     'type' => 'express',  
        //     'requested_capabilities'=> ['card_payments', 'transfers','legacy_payments'],            
        // ]);
        // $account = $stripe->accounts->create([
        //     'type' => 'express',
        //     'card_payments' => ['requested' => true],
        //     'transfers' => ['requested' => true],
        //     'country' => 'US',
        // ]);
        $account = $stripe->accounts->create([
            'type' => 'custom',
            'country' => 'US',
            // 'email' => 'jenny.rosen@example.com',
            'capabilities' => [
              'card_payments' => ['requested' => true],
              'transfers' => ['requested' => true],
            ],
          ]);

        $user->stripe_account_id = $account->id;
    }
}
