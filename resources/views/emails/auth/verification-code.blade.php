@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <p>Dear {{$user->name}}</p>
        <p>We have received a request to reset your password for your account at NotNew. If you made this request, please enter the following verification code to proceed:</p>
        <h4>Verification Code:</h4>
        @component('mail::button', [ 'url' => $verificationUrl , 'color' => 'blue', 'target' => '_blank']) 
        <button style="background-color: #ec2a8b; border:0px; border-radius:4px; color:white;padding:12px;font-size:22px; padding-left:60px;padding-right:60px;">{{$verificationUrl}}</button> 
        <p>If you did not make this request, please ignore this email and ensure that your account is secure by reaching out at <a href="mailto:support@NotNew.com">support@NotNew.com</a>.</p>
        <p>Thank you for choosing NotNew.</p>
        <br />
        <p>Best regards,</p>
        <p><b>NotNew support team</b></p>
        <p>NotNew</p>
    </div>
@endcomponent