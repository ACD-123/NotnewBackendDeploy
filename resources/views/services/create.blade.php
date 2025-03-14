@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('services.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required="true" placeholder="Enter Service Name">
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.00" min="0" required="true" name="price" class="form-control"
                       placeholder="Enter Service Price $" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required="true" class="form-control">
                    <option value="" selected>Please select...</option>
                    @foreach($category as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" required="true" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1">Active</option>
                    <option value="0">In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" required="true" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Service Description">
            </textarea>
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
