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

class serviceApproved extends Notification
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
        ->subject($notifiable->name . '- Your Ad is Now Live on NotNew!')
        ->markdown('emails.ServiceApproved.add', [
            'user' => $notifiable,
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
            'message' => 'Your Add has been Approved!',
            'url' => '/service/'.$service->guid,
        ];
    }
}
