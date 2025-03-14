<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageSent extends Notification
{
    use Queueable;

    protected $recipientId;
    protected $productId;
    protected $sender;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notifiable)
    {
        $this->recipientId = $notifiable->recipientId;
        $this->productId = $notifiable->productId;
        $this->sender = $notifiable->sender;
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
        ->subject($notifiable->name . ' - Message')
        ->markdown('emails.message.sent', [
            'user' => $notifiable,
            'verificationUrl' => $url.'/product/ask/'.$this->recipientId.'/'.$this->productId
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
            'message' => $this->sender. ' has sent you a message',
            'url' => '/product/ask/'.$this->recipientId.'/'.$this->productId
        ];
    }
}
