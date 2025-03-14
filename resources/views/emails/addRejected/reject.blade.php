@component('mail::message')
    <div>
        <p>Dear {{$user->name}},</p>
        <p>We hope this email finds you well.</p>
        <p>We regret to inform you that your product listing submission on NotNew has been rejected. We understand that this may be disappointing, but we want to ensure that all products listed on our platform meet our quality standards and guidelines.</p>
        <p>The reasons for the rejection of your product listing may include:</p>
        <ol>
            <li>Incomplete or inaccurate product information.</li>
            <li>Violation of our platform's terms of service or prohibited items policy.</li>
            <li>Quality or safety concerns related to the product.</li>
        </ol>
        <p>
            We encourage you to review our seller guidelines and make any necessary adjustments to your product listing before resubmitting it for review. Our goal is to provide a positive and safe shopping experience for our users, and we appreciate your cooperation in maintaining the quality of products on our platform.
        </p>
        <p>
            If you have any questions or need further clarification regarding the rejection of your product listing, please don't hesitate to contact our seller support team at <a href="mailto:seller-support@notnew.com">seller-support@notnew.com</a>. We're here to assist you and ensure a smooth process for listing your products on NotNew.
        </p>
        <p>
            Thank you for your understanding and cooperation.
        </p>
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
    </div>
@endcomponent