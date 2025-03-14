<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\Order;
use App\Models\UserOrder;
use App\Models\Media;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Models\Prices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BidAccepted extends Notification
{
    use Queueable;

    protected  $bid;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->bid = $bid;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();
        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Bid Accepted')
            ->markdown('emails.order.buyers.placed', ['user' => $notifiable]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
