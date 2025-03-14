@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <h3>{{$user->name}} Needs Help</h3>
        <p>Dear Admin</p>
       <p>{{$message}}</p>
        <p>Best regards,</p>
        <p>NotNew Support</p>
    </div>
@endcomponent