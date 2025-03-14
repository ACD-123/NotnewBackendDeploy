<?php

namespace App\Notifications;
use App\Helpers\ArrayHelper;
use App\Mail\BaseMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Otp;
class ForgetPasswordVerification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $verificationCode = ArrayHelper::otpGenerate();
        $otp = new Otp();
        $otp->otp = $verificationCode;
        $otp->email = $notifiable->email;
        $otp->name = $notifiable->name;
        $otp->otp_type = "ForgetPassword";
        $otp->save();
        $baseMailable = new BaseMailable();
        
        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Password Rest Email')
            ->markdown('emails.auth.verification-code', [
                'user' => $notifiable,
                'verificationUrl' => $verificationCode]);
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
