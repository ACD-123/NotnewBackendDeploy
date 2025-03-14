<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\Order;
use App\Models\Media;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Models\Prices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeleteAccount extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        // $this->product = $product;
        $this->user = $user;
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
        $user = $this->user;

        return $baseMailable->to('Support@NotNew.com')
        ->subject($notifiable->name . '- Account Delete Request!')
        ->markdown('emails.deleteAccount.deleteAccount', [
            'user' => $user
        ]);
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
