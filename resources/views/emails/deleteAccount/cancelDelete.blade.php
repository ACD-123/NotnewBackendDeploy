@component('mail::message')
<div>
  <h2>Account Cancel Delete Request</h2>
<div>
  <h2>Hello Admin!</h2>
  <p>
    User {{ $user->name }} with Id "{{$user->id}}" is Requesting for Cancel Delete Account.
  </p>
  <p>
    Thanks!!
  </p>
</div>
@endcomponent