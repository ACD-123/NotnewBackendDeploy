@extends('adminlte::page')

@section('content')

    <div class="container">

        <h3 class="text-center mb-5">Delivery Company</h3>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('deliverycompany.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('deliverycompany.search')}}" method="GET">
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
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($deliverycompany as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>
                        <a href="{{route('deliverycompany.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                                <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                    </td>
                </tr>   
                @include('partials.delete-modal',['data' => $item,'route'=> "deliverycompany"])
              @empty
                <p>No Delivery Company</p>
                @endforelse
            </tbody>
        </table>
        {{$deliverycompany->links()}}
    </div>
@endsection

