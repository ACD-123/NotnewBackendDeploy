<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Helpers\ArrayHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use App\Models\Otp;

class RegistrationVerificationNotification extends Notification
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
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param $notifiable
     * @return BaseMailable
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationOTP($notifiable);
        $verificationCode = ArrayHelper::otpGenerate();
        $otp = new Otp();
        $otp->otp = $verificationCode;
        $otp->email = $notifiable->email;
        $otp->otp_type = "EmailVerification";
        $otp->name = $notifiable->name;
        $otp->save();

        $baseMailable = new BaseMailable();

        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Registration Activation')
            ->markdown('emails.auth.registration-activation', [
                'user' => $notifiable,
                'otp'=> $verificationCode,
                'verificationUrl' => $verificationUrl]);
    }

    protected function verificationOTP($notifiable)
    {
        $domain = env('FRONT_END_URL') . 'emailverification';
        $envKey = env('APP_KEY');
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));
        $key = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification() . $envKey);
        $url = "$domain/$key/$hash/expires=$expires/$notifiable->email";
        return $url;
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $domain = env('FRONT_END_URL') . 'user/verify';
        $envKey = env('APP_KEY');
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));
        $key = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification() . $envKey);
        $url = "$domain/$key/$hash/?expires=$expires";
        return $url;
//        return URL::temporarySignedRoute(
//            'verification.verify',
//            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
//            [
//                'id' => $notifiable->getKey(),
//                'hash' => sha1($notifiable->getEmailForVerification()),
//            ]
//        );
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
            //
        ];
    }
}
