@extends('adminlte::page')

@section('content')
    <h3 class="text-center mb-5">PRICES</h3>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('price.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('prices.search')}}" method="GET">
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
                <th scope="col">Value</th>
                <th scope="col">Status</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
           
            @foreach($prices as $price)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$price->name}}</td>
                    <td>{{$price->value}}</td>
                    <td><span
                            class="{{$price->active == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$price->active == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span>
                    </td>
                    <td>{{$price->created_at}}</td>
                    <td>
                        <a href="{{route('price.edit', $price->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                                <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$price->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                        <!-- <form action="{{ route('price.destroy', $price->id) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                        @include('partials.delete-modal',['data' => $price,'route'=> "price"])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$prices->links()}}
        
    </div>
@endsection
