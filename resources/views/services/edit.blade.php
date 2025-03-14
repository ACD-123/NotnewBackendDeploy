@extends('adminlte::page')

@section('content')
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('services.update',$service->id)}}" method="POST">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter Service Name"
                       value="{{$service->name}}">
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.00" min="0" name="price" class="form-control"
                       placeholder="Enter Service Price $" value="{{$service->price}}" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    @foreach($category as $item)
                        <option @if($service->category_id == $item->id) selected  @endif value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$service->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$service->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Service Description">{{$service->description}}</textarea>
            </div>
            @IF(count($media))
                <div class="form-group">
                <label>Media</label>
                    @foreach($media as $item)
                        <div><img src="{{$item->url}}" alt="{{$item->guid}}" width="200" height="200"/></div>
                    @endforeach
                </div>
            @ENDIF
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
