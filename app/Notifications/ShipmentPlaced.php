<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\ShippingDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentPlaced extends Notification
{
    use Queueable;

    protected $shipment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ShippingDetail $shipment)
    {
        $this->shipment = $shipment;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();

        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Shipment Placed')
            // @TODO change shipment number to an actual modal call 
            ->markdown('emails.shipment.placed', ['user' => $notifiable, 'shipment' => '123045']);
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
