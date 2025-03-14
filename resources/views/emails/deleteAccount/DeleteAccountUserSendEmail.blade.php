@component('mail::message')
<div>
  <h2>Account Delete Request</h2>
<div>
<h2>Dear {{ $user->name }},</h2>

<p>We have received your request to delete your account and would like to inform you that your account will
     be deleted within 5 to 10 working days.</p>

<p>If you have changed your mind and would like to keep your account, please let us know by contacting our 
    support team at <a href="mailto:support@NotNew.com">support@NotNew.com</a>. We would be happy 
    to assist you with any concerns or issues you may have.</p>

<p>Thank you for using NotNew. We hope that you have enjoyed your experience with us 
    and that you will consider using our platform again in the future.</p>

<p>Best regards,</p>

NotNew Support
  
</div>
@endcomponent