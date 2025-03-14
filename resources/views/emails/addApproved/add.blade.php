@component('mail::message')
  <div>
        <p>Dear {{$user->name}} ,</p>
        <p>We hope this email finds you well.</p>
        <p>We're pleased to inform you that your product listing on NotNew has been approved and is now live on our platform. Congratulations!</p>
        <p>Your product has met our quality standards and is now available for customers to discover and purchase. </p>
        <p>Thank you for choosing NotNew as your selling platform. We look forward to seeing your products thrive on our platform and to continued collaboration in the future.</p>
        <p>If you have any questions or need further assistance, please feel free to contact our seller support team at <a href="mailto:seller-support@notnew.com">seller-support@notnew.com</a> We're here to help you succeed..</p>
       
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
    </div>
@endcomponent