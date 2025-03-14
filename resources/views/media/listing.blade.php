@extends('adminlte::page')

@section('title', 'List Media')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mt-2 p-4">
                    <h1> Media List
                        <small><a class="btn btn-success float-right" href="{{route('media.create')}}">Upload
                                image</a>
                        </small>
                    </h1>
                    <ul class="list-group">
                        @foreach($media as $medium)
                            <li class="border clearfix list-group-ite">
                                <img class="border img-fluid" width="200px" src="{{asset('storage/'.$medium->name)}}"/>
                                <b><a href="{{$medium->url}}" target="_blanck">Download</a></b>
                                <div class="float-right">
                                <button type="button"
                                    class="btn btn-danger"
                                    data-toggle="modal" data-target="#products1{{$medium->id}}">
                                    <i class="fa fa-trash" style="color: white"></i>
                                </button>
                                    <!-- <form class="d-inline"
                                          action="{{route('media.destroy',$medium->id)}}"
                                          method="POST">
                                        @method('DELETE')
                                        @csrf
                                        <button class="btn btn-danger">Delete</button>
                                    </form> -->
                                    <a href="{{route('media.edit',$medium->id)}}"
                                       class="btn btn-primary">edit</a>
                                </div>
                            </li>
                            @include('partials.delete-modal',['data' => $medium,'route'=> "media"])
                        @endforeach
                    </ul>
                </div>
            </div>
            .
        </div>
    </div>
@endsection
