@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <p>Dear {{$user->name}}</p>
        <p>You have SuccessFull Register WIth Not New</p>
        <p>If you have any issue then kindly contact with <a href="mailto:support@NotNew.com">support@NotNew.com</a>.</p>
        <p>Thank you for choosing NotNew.</p>
        <br />
        <p>Best regards,</p>
        <p><b>NotNew support team</b></p>
        <p>NotNew</p>
    </div>
@endcomponent