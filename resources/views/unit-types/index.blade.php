@extends('adminlte::page')

@section('content')
    <h3 class="text-center mb-5">UNIT TYPES</h3>


    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('unit-type.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('category.search')}}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" placeholder="Search"/>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table">
            <br>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">status</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;

            @endphp

            @foreach($unitTypes as $unitType)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$unitType->name}}</td>

                    <td>
                        <span class="{{$unitType->active == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$unitType->active == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span>
                    </td>

                    <td>
                        <a href="{{route('unit-type.edit', $unitType->id)}}" class="btn btn-info"><i
                                    class="fa fa-pen"></i></a>
                            <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$unitType->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                        <!-- <form action="{{ route('unit-type.destroy', $unitType->id) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                    </td>
                </tr>
                @include('partials.delete-modal',['data' => $unitType,'route'=> "unit-type"])
            @endforeach
            </tbody>
        </table>
        {{$unitTypes->links()}}
    </div>
@endsection
