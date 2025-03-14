@component('mail::message')
    <div>
    <p>Dear {{$user->name}}</p>
        <p>
            Thank you for registering with NotNew.
        </p>
        <p>
            To complete your verification process and ensure the security of your account, we kindly ask you to verify your email address. You can do this by clicking on the link provided below or copying and pasting the provided code into your web browser/Mobile App.
        </p>
        <p>
            Please note that your email for accessing the NotNew platform is {{$user->email}}
        </p>
        <p>Your OTP Code: {{$otp}}</p>
       <!--<p>-->
        <!--    @component('mail::button', ['url' => $verificationUrl, 'color' => 'blue', 'target' => '_blank'])-->
        <!--    Verification Link: {{$user->email}} <a href="{{$verificationUrl}}">Verify Email Address</a>-->
        <!--</p>-->
        <p>
            If you encounter any difficulties with the verification process or have any questions,
             please don't hesitate to contact our support team at <a href="mailto:support@notnew.com">support@notnew.com</a>.</p>
        </p>
        <p>Thank you for choosing NotNew.</p>
    </div>
@endcomponent