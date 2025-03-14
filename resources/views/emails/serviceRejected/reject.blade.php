@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <p>Dear {{$user->name}},</p>
        <p>We wanted to let you know that we have reviewed your ad on NotNew and unfortunately, we are unable to approve it at this time.</p>
        <p>Our team carefully reviewed your listing and found that it did not meet our standards for quality and accuracy. Some common reasons for ad rejection include inaccurate or incomplete service descriptions, low-quality images, or listing items that are not allowed on our platform.</p>
        <p>We encourage you to review your ad and make any necessary updates or changes. Once you've made the necessary updates, you can resubmit your ad for review.</p>
        <p>If you have any questions or concerns about your ad or the review process, please don't hesitate to reach out to our customer support team at<a href="mailto:support@NotNew.com">support@NotNew.com</a>. We're always here to help and support you in any way we can.</p>
        <p>Best regards,</p>
        <p><b>NotNew Support Team</b></p>
    </div>
@endcomponent