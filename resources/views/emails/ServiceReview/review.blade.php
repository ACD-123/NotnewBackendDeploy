@component('mail::message')
  <div style="background-color: #f9d9eb; padding:20px;">
        <p>Dear {{$user->name}},</p>
        <p>Thank you for posting your ad on NotNew. We are excited to help you showcase your products to our community of buyers.</p>
        <p>We wanted to let you know that your ad is currently in review and will be listed on our platform as soon as it is approved. We appreciate your patience while our team carefully reviews your listing to ensure that it meets our standards for quality and accuracy.</p>
        <p>If your ad meets our guidelines, it will be live on our platform shortly. You will receive an email notification once your ad is approved and available for buyers to view and purchase.</p>
        <p>Thank you for choosing NotNew. We look forward to seeing your ad live on our site soon!</p>
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
  </div>
@endcomponent