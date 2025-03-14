@extends('adminlte::page')

@section('content')
    <div class="container-lg">
    <h3 class="text-center mb-5">User Details</h3>
        @if(session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
        @endif
    <form action="{{route('user.updateUser', $user->id)}}" method="POST">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label>Name</label>
            <label class="form-control" >{{ $user->name }}</label>
        </div>
        <div class="form-group">
            <label>Email</label>
            <label class="form-control" >{{ $user->email }}</label>
        </div>
        <div class="form-group">
            <label>Stripe Account ID</label>
            <label class="form-control" >{{ $user->stripe_account_id }}</label>
        </div>
        <div class="form-group">
            <label>Email Verified At</label>
            <label class="form-control" >{{ $user->email_verified_at }}</label>
        </div>
         <div class="form-group">
            <label>Phone</label>
            <input type="text" readonly name="phone" class="form-control" value="{{ $user->phone }}" />
        </div>
        <div class="form-group">
            <label>Customer Stripe ID</label>
            <label class="form-control" >{{ $user->customer_stripe_id }}</label>
        </div>
        <div class="form-group">
            <label> Trusted Seller</label>
            <label class="form-control" >{{ $user->isTrustedSeller == 0 ? 'No' : 'Yes' }}</label>
        </div>
        @if($user->profile_image)
        <div class="form-group">
            <label>Profile Image</label>
            <br />
            <img style="width: 100px; height: 100px;"  src="/{{$user->profile_image}}" />
        </div>
        @endif
        <div class="form-group">
                <label>Auto Active</label>
                <select name="is_autoAdd" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$user->is_autoAdd == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$user->is_autoAdd == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$user->status == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$user->status == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
        <input type="submit" class="btn btn-success" value="Submit" />
    </form>
    
    </div>
@endsection