@extends('adminlte::page')

@section('content')

    <h3 class="text-center mb-5">IN-ACTIVE CATEGORIES</h3>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('category.create')}}" class="btn btn-primary">Add New</a>
                <form id="form-submit" role="form" action="{{route('categories.active-all')}}" method="POST">
                    {{ csrf_field()}}
                    <button class="btn btn-success"><i class="fa fa-key"></i> Activate
                        All Categories
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('category.inactive.search')}}" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control" placeholder="Search"/>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        {{ $category}}
        <table class="table">
            <br>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Type</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($category as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->description}}</td>
                    <td><span class="{{$item->active == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$item->active == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span></td>
                    <td><span class="badge badge-primary">{{$item->type}}</span></td>
                    <td>{{$item->created_at}}</td>
                    <td>
                        <a href="{{route('category.edit', $item->id)}}" class="btn btn-info"><i class="fa fa-pen"></i></a>
                        <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                        <!-- <form id="form-submit" action="{{ route('category.destroy', $item->id) }}" method="POST">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash" style="color: white"></i></button>
                        </form> -->
                        <form id="form-submit" action="{{route('category.update',$item->id)}}"
                              method="POST" id="form-submit">
                            {{ method_field('PATCH') }}
                            {{ csrf_field()}}
                            <input type="hidden" name="activateOne" value="activateOnlyOne">
                            <input type="hidden" name="active" value="true">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fa fa-key"></i></button>
                        </form>
                    </td>
                </tr>
                @include('partials.delete-modal',['data' => $item,'route'=> "category"])
            @empty
                <p>No In-Active Categories</p>
            @endforelse
            </tbody>
        </table>
        {{$category->links()}}
    </div>
@endsection
{{--public css was not rendering properly this is the reason why i put this here--}}
<style>
    #form-submit {
        display: unset;
    }
</style>

