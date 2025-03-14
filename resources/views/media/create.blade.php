@extends('adminlte::page')

@section('title', 'create media')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mt-2 p-4">
                    <h1>Create Settings </h1>
                    <form method="POST" action="{{route('media.store')}}" enctype="multipart/form-data">
                        @csrf
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

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
