@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('banks.store')}}" method="POST"  enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="fullname" class="form-control" placeholder="Enter Bank Name">
            </div>

            <div class="form-group">
                <label>Short Name</label>
                <input type="text" name="shortname" class="form-control" placeholder="Enter Short Name">
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="phone" name="phone" class="form-control" placeholder="Enter Phone No">
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" placeholder="Enter Address"></textarea>
            </div>
            <div class="form-group">
                <label>Zip</label>
                <input type="text" name="zip" class="form-control" placeholder="Enter Zip Code">
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
