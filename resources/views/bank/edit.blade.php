@extends('adminlte::page')

@section('content')

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('banks.update',$bank->id)}}" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="fullname" value="{{$bank->fullname}}" class="form-control" placeholder="Enter Bank Name">
            </div>

            <div class="form-group">
                <label>Short Name</label>
                <input type="text" name="shortname" value="{{$bank->shortname}}" class="form-control" placeholder="Enter Short Name">
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="phone" name="phone" value="{{$bank->phone}}" class="form-control" placeholder="Enter Phone No">
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" placeholder="Enter Address"> {{$bank->address}}</textarea>
            </div>
            <div class="form-group">
                <label>Zip</label>
                <input type="text" name="zip"  value="{{$bank->zip}}" class="form-control" placeholder="Enter Zip Code">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

@endsection
