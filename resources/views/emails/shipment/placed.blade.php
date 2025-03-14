@component('mail::message')
    <div>
        <p>Dear {{$user->name}}</p>
        <p>Your shipment # {{$shipment}} has been placed</p>
    </div>
@endcomponent
