@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('category.add-attributes',$category->guid)}}" method="POST">
            @csrf
            <div class="form-group">
                <label for="properties">Property</label>
                @include('attributes.lookup')
            </div>
            {{--<div class="form-group">--}}
                {{--<label for="properties">Category</label>--}}
                {{--@include('category.lookup')--}}
            {{--</div>--}}
            <div class="form-group">
                <label for="properties">Unit Type</label>
                @include('unit-types.lookup')
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="value" placeholder="Enter Description" required>
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
@section('js')
    <script>
    </script>
@stop
