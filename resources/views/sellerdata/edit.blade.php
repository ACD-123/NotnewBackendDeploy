@extends('adminlte::page')

@section('content')

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('sellerdata.update',$sellerdata->id)}}" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <label name="fullname" class="form-control">{{$sellerdata->fullname}}</label>
            </div>
            <div class="form-group">
                <label>Email</label>
                <label name="email" class="form-control">{{$sellerdata->email}}</label>            
            </div>
            <div class="form-group">
                <label>Phone</label>
                <label name="phone" class="form-control">{{$sellerdata->phone}}</label>            
            </div>

            <div class="form-group">
                <label>Address</label>
                <label name="address" class="form-control">{{$sellerdata->address}}</label>            
            </div>

            <div class="form-group">
                <label>Zip</label>
                <label name="zip" class="form-control">{{$sellerdata->zip}}</label>            
            </div>

            <div class="form-group">
                <label>Description</label>
                <label name="description" class="form-control">{{$sellerdata->description}}</label>            
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$sellerdata->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$sellerdata->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Featured</label>
                <select name="featured" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$sellerdata->featured == 1 ? 'selected' : ''}}>Featured</option>
                    <option value="0" {{$sellerdata->featured == 0 ? 'selected': ''}}>Non Featured </option>
                </select>
            </div>
            <img src="/{{$sellerdata->cover_image}}" width="150" height="150">
            
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
