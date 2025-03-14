@component('mail::message')
<div>
  <h2>Your Bid Has Been Confirmed</h2>
  <p>Dear {{ $user->name }},</p>
  <p>Your Bid has been Confimred Kindly go to you profile and Purchase the Item You Bid!</p>
  <hr />
  <p>If you have any questions or concerns about your order, please feel free to contact us at <a href="mailto:support@NotNew.com">support@NotNew.com</a>.</p>
  <p>Best regards,</p>
  <p>NotNew Support Team</p>
</div>
<style>
  body{
    font-size: 13px;
  }
  table{
    width:100%;
  }
  /* table td {
    border: 1px solid #000;
  } */
</style>
@endcomponent