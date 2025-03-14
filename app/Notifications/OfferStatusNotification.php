<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferStatusNotification extends Notification
{
    use Queueable;

    protected $status;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        $this->status = $status->status;
        $this->user = $status->name;
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
        $status = $this->status;
        $url = env('FRONT_END_URL', 'https://notnew.apextechworldllc.com/');

        return $baseMailable->to($notifiable->email)
        ->subject($status ? $notifiable->name . '- Offer Accepted' : $notifiable->name . '- Offer Rejected')
        ->markdown('emails.offer.status', [
            'user' => $notifiable,
            'status' => $status,
            'verificationUrl' => $url.'/user/offers'
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
        $status = $this->status;

        return [
            'message' => $status ? $this->user.' has accepted your offer' : $this->user.' has rejected your offer',
            'url' => '/user/offers'
        ];
    }
}
