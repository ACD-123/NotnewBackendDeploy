@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <h3>Welcome to NotNew</h3>
        <p>Dear {{$user->name}}</p>
        <p>We are excited to welcome you to NotNew, a platform that offers a variety of options for buying and selling items online. Thank you for choosing us to meet your buying and selling needs.</p>
        <p>As a registered user, you can now access a wide range of features on our platform, including creating your listings, browsing and purchasing available items, and communicating with other users.</p>
        <p>At NotNew, we strive to make the process of buying and selling easy and convenient for our users. We believe that our platform offers the best options for both buyers and sellers.</p>
        <p>If you have any questions or concerns, please do not hesitate to contact our support team at <a href="mailto:support@NotNew.com">support@NotNew.com</a>. We are always here to help.</p>
        <p>Thank you again for choosing NotNew. We look forward to helping you find the perfect items you're looking for.</p>
        <p>Best regards,</p>
        <p>NotNew Support</p>
    </div>
@endcomponent