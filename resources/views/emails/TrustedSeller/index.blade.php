@component('mail::message')
  <div style="background-color: #f9d9eb; padding:20px;">
        <p>Dear {{$user->name}},</p>
        <p>Thank you for posting your ad on NotNew. But we are noteticing that you haven't connect to Account Kindly go to the Accounts Page of your Profile for activation!!</p>
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
  </div>
@endcomponent