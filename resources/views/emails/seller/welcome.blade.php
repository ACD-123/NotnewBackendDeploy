@component('mail::message')
  <div>
        <p>Dear {{$user->name}},</p>
        <p>Welcome to NotNew! We're thrilled to have you join our platform as a seller.</p>
        <p>Your seller's account has been successfully created, and you're now ready to start listing your products on NotNew. With our platform, you'll have access to a wide range of tools and features designed to help you reach more customers and grow your business.</p>
        <p>Upon logging in, you'll be prompted to create a new password for your account. Please ensure to choose a strong and secure password to protect your account information.</p>
        <p>
          If you have any questions or need assistance with setting up your seller's account, please don't hesitate to reach out to our seller support team at <a href="mailto:seller-support@notnew.com">seller-support@notnew.com</a>. We're here to help you every step of the way.
        </p>
        <p>Thank you for choosing NotNew as your platform for selling. We're excited to see your products thrive on our platform!</p>
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
  </div>
@endcomponent