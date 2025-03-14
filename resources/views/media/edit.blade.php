@extends('adminlte::page')

@section('title', 'create media')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mt-2 p-4">
                    <h1>Edit Media </h1>
                    <form method="POST" action="{{route('media.update',$media->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <b>{{$media->url}}</b>

                        <img class="border img-fluid" width="200px" src="{{$media->url}}"/>
                        <div class="form-group">
                            <label for="file">file</label>
                            <input type="file" name="file" class="form-control-file" id="file">
                        </div>


                        <div class="form-group">
                            <label for="active">
                                Active
                            </label>
                            <input name="active" checked type="checkbox" value=1 id="active">

                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <div class="alert alert-danger" role="alert">
                            Please take care of Image extension
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
