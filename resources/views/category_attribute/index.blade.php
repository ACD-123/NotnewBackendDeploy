
@extends('adminlte::page')
@inject('model','App\Models\CategoryAttributes')

@section('content')

    <div class="container">

        <h3 class="text-center mb-5">CATEGORIES Attributes</h3>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('category.create')}}" class="btn btn-primary">Add New</a>
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
                <th scope="col">Category Name</th>
                <th scope="col">Attribute Name</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($categoryAttributes as $item)
                <tr>
                    <td>{{$count++}}</td>

                    <td>{{$item->category->name}}</td>
                    <td>{{$item->attribute->name}}</td>
                    <td>{{$item->created_at}}</td>
                    <td>
                      {{--  <a href="{{route('category.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                            <a href="{{route('category.show-list', $item->guid)}}" class="btn btn-info"><i
                                    class="fa fa-pen"></i>add/update properties</a>
                                <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button> --}}
                        <!-- <form action="{{ route('category.destroy', $item->id) }}" method="POST" style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                    </td>
                </tr>   
                @include('partials.delete-modal',['data' => $item,'route'=> "category"])
              @empty
                <p>No Categories Attribute</p>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

