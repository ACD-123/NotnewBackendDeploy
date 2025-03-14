@extends('adminlte::page')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('category.show-attributes',$category->guid)}}" class="btn btn-primary">Add
                    Properties</a>
            </div>
            <!-- <div class="col-md-4 text-right">
                <form action="{{route('category.show-single-attributes')}}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" placeholder="Search"/>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>
            </div> -->
        </div>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                {{--<th scope="col">Description</th>--}}
                {{--<th scope="col">Status</th>--}}
                <th scope="col">Type</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($category->categoryAttributes as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->attribute->name}}</td>
                    <td>{{$item->unitType ? $item->unitType->name : null}}</td>

                    <td>{{$item->created_at}}</td>
                    <td>
                        <!-- <a href="{{route('category.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a> -->
                        <!-- <a href="{{route('category.show-list', $category->guid)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i>add properties</a> -->
                        <!-- <form action="{{ route('category.delete-attributes', $item->id) }}" method="POST" style="display: unset">
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                        <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                    </td>
                </tr>
                @include('partials.delete-modal-attributes',['data' => $item,'route'=> "properties"])
            @empty
                <p>No Active Properties</p>
            @endforelse
            </tbody>
        </table>

    </div>
@endsection


@section('js')
    <script>

    </script>
@stop
