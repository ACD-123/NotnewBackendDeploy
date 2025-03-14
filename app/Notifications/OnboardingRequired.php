<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Stripe\AccountLink;
use Stripe\StripeClient;

class OnboardingRequired extends Notification
{
    use Queueable;

    /** @var AccountLink $accountLink */
    protected $accountLink;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(AccountLink $accountLink)
    {
        $this->accountLink = $accountLink;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Complete Onboarding',
            'url' => $this->accountLink->url,
            'stripe_account_id' => $notifiable->stripe_account_id
        ];
    }
}
