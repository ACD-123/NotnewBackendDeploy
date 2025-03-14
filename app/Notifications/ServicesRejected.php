<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\Order;
use App\Models\Media;
use App\Models\Service;
use App\Models\ShippingDetail;
use App\Models\Prices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicesRejected extends Notification
{
    use Queueable;

    protected $status;
    protected $user;
    protected $service;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($service)
    {
        // $this->product = $product;
        // $this->status = $status->status;
        // $this->user = $status->name;
        $this->service = $service;
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
     * @return
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();
        // $status = $this->status;
        $service = $this->service;
        $url = env('FRONT_END_URL', 'https://notnew.apextechworldllc.com/');

        return $baseMailable->to($notifiable->email)
        ->subject($notifiable->name .'- Your Ad has been rejected')
        ->markdown('emails.serviceRejected.reject', [
            'user' => $notifiable,
            // 'status' => $status,
            'verificationUrl' => $url.'/service/'.$service->guid,
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
        $service = $this->service;
        return [
            'message' => 'Your Add has been Rejected!',
            'url' => '/service/'.$service->guid,
        ];
    }
}
