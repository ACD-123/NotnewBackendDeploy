<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferMadeNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $price;
    protected $sender;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($offer)
    {
        $this->product = $offer->product;
        $this->price = $offer->price;
        $this->sender = $offer->sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();
        $url = env('FRONT_END_URL', 'https://notnew.apextechworldllc.com/');

        return $baseMailable->to($notifiable->email)
        ->subject($notifiable->name . '- Offer Made')
        ->markdown('emails.offer.made', [
            'user' => $notifiable,
            'verificationUrl' => $url.'/user/offers']);
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
            'message' => $this->sender.' has made an offer of '.$this->price.' for '.$this->product,
            'url' => '/user/offers'
        ];
    }
}
