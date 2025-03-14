@extends('adminlte::page')

@section('content')
<div class="container">
    <form action="{{route('unit-type.store')}}" method="POST">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter Unit Type Name">
        </div>


        {{--<div class="form-group">--}}
        {{--<label>Status</label>--}}
        {{--<select name="active" class="form-control">--}}
        {{--<option value="" selected >Please select...</option>--}}
        {{--<option value=1>Active</option>--}}
        {{--<option value=0>In-Active</option>--}}
        {{--</select>--}}
        {{--</div>--}}

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
