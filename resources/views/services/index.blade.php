@extends('adminlte::page')

@section('content')
    <h3 class="text-center mb-5">SERVICES</h3>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('services.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('services.search')}}" method="GET">
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
                <th scope="col">Service Name</th>
                <th scope="col">Status</th>
                <th scope="col">Price</th>
                <th scope="col">Created By</th>
                {{-- <th scope="col">Category</th>--}}
                {{-- <th scope="col">Created At</th>--}}
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp

            @forelse($services as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>
                        <button type="button"
                                class="{{$item->active  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                                data-toggle="modal" data-target="#products{{$item->id}}">
                            {{$item->active == 1 ? 'Active' : 'Un-Active'}}
                        </button>
                    </td>
                    <td>$ {{$item->price}}</td>
                    <td>
                        <a href="{{route('customer.services',$item->user->id)}}">{{$item->user->name}}</a>
                    </td>

                    {{-- <td>{{$item->category->name}}</td>--}}
                    {{-- <td>{{$item->created_at}}</td>--}}
                    <td>
                        <a href="{{route('services.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                        <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                        <!-- <form action="{{ route('services.destroy', $item->id) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                    </td>
                </tr>
                @include('partials.status-modal',['data' => $item,'route' => "services"])
                @include('partials.delete-modal',['data' => $item,'route'=> "services"])
            @empty
                <p>No Active Services</p>
            @endforelse
            </tbody>
        </table>
        {{$services->links()}}
    </div>
@endsection
