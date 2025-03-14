@component('mail::message')
    <div style="background-color: #f9d9eb; padding:20px;">
        <p>Hello {{$user->name}}</p>
        <p>{{$user->sender}} sent you a message</p>
        @component('mail::button', [ 'url' => $verificationUrl , 'color' => 'blue', 'target' => '_blank']) 
        <a href={{$verificationUrl}} style="text-decoration: none; background-color: #ec2a8b; border:0px; border-radius:4px; color:white;padding:12px;font-size:22px; padding-left:60px;padding-right:60px;">See Message</a> 
        @endcomponent
        <p> Please remember your email address is your username.</p>
    </div>
@endcomponent