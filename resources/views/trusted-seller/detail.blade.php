@extends('adminlte::page')

@section('content')
    <div class="container-lg">
        @if(session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
        @endif
    <!--<form action="{{route('user.changeStatus', $user->id)}}" method="POST">-->
    <!--    <input type="hidden" name="_method" value="POST">-->
    <!--    <input type="hidden" name="_token" value="{{ csrf_token() }}">-->
    <!--    <div class="form-group">-->
    <!--        <label>Name</label>-->
    <!--        <label class="form-control" >{{ $user->name }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Email</label>-->
    <!--        <label class="form-control" >{{ $user->email }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Email</label>-->
    <!--        <label class="form-control" >{{ $user->email }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Address</label>-->
    <!--        <label class="form-control" >{{ $user->address }}</label>-->
    <!--    </div>-->
    <!--     <div class="form-group">-->
    <!--        <label>Number</label>-->
    <!--        <label class="form-control" >{{ $user->number }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Store</label>-->
    <!--        <label class="form-control" >{{ $user->store }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Facebook</label>-->
    <!--        <label class="form-control" >{{ $user->facebook }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Instagram</label>-->
    <!--        <label class="form-control" >{{ $user->instagram }}</label>-->
    <!--    </div>-->

    <!--    <div class="form-group">-->
    <!--        <label>EIN</label>-->
    <!--        <label class="form-control" >{{ $user->ein }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>SSN</label>-->
    <!--        <label class="form-control" >{{ $user->ssn }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Business Type</label>-->
    <!--        <label class="form-control" >{{ $user->businessType }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Courrier Type</label>-->
    <!--        <label class="form-control" >{{ $user->courriertype }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Website</label>-->
    <!--        <label class="form-control" >{{ $user->website }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Shipment Type</label>-->
    <!--        <label class="form-control" >@if( $user->shipmenttype == 2) {{'Ship YourSelf'}} @else {{'Ship With Flexe Market'}} @endif</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Shipment Price</label>-->
    <!--        <label class="form-control" >{{ $user->price }}</label>-->
    <!--    </div>-->
    <!--    <div class="form-group">-->
    <!--        <label>Shipment Days</label>-->
    <!--        <label class="form-control" >{{ $user->days }}</label>-->
    <!--    </div>-->
    <!--    <input type="submit" value="Submit" />-->
    <!--</form>-->
    <form action="{{route('user.changeStatus', $user->id)}}" method="POST">
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
            <label>Email</label>
            <label class="form-control" >{{ $user->email }}</label>
        </div>
        <div class="form-group">
            <label>Address</label>
            <label class="form-control" >{{ $user->address }}</label>
        </div>
         <div class="form-group">
            <label>Phone</label>
            <label class="form-control" >{{ $user->phone }}</label>
        </div>
        <div class="form-group">
            <label>Store</label>
            <label class="form-control" >{{ $user->shop->fullname }}</label>
        </div>
        <!-- <div class="form-group">
            <label>Facebook</label>
            <label class="form-control" >{{ $user->facebook }}</label>
        </div>
        <div class="form-group">
            <label>Instagram</label>
            <label class="form-control" >{{ $user->instagram }}</label>
        </div>

        <div class="form-group">
            <label>EIN</label>
            <label class="form-control" >{{ $user->ein }}</label>
        </div>
        <div class="form-group">
            <label>SSN</label>
            <label class="form-control" >{{ $user->ssn }}</label>
        </div> -->
        <!-- <div class="form-group">
            <label>Business Type</label>
            <label class="form-control" >{{ $user->businessType }}</label>
        </div>
        <div class="form-group">
            <label>Courrier Type</label>
            <label class="form-control" >{{ $user->courriertype }}</label>
        </div> -->
        <div class="form-group">
            <label>Website</label>
            <label class="form-control" >{{ $user->shop->website }}</label>
        </div>
        <!-- <div class="form-group">
            <label>Shipment Price</label>
            <label class="form-control" >{{ $user->price }}</label>
        </div>
        <div class="form-group">
            <label>Shipment Days</label>
            <label class="form-control" >{{ $user->days }}</label>
        </div> -->
        <input type="submit" value="Submit" />
    </form>
    
    </div>
@endsection