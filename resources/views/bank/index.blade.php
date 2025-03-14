@extends('adminlte::page')

@section('content')

    <div class="container">

        <h3 class="text-center mb-5">Banks</h3>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <a href="{{route('banks.create')}}" class="btn btn-primary">Add New</a>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('banks.search')}}" method="GET">
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
                <th scope="col">Short Name</th>
                <th scope="col">Phone</th>
                <th scope="col">Address</th>
                <th scope="col">Zip</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($bank as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->fullname}}</td>
                    <td>{{$item->shortname}}</td>
                    <td>{{$item->phone}}</td>
                    <td>{{$item->address}}</td>
                    <td>{{$item->zip}}</td>
                    <td>
                        <a href="{{route('banks.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                                <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                    </td>
                </tr>   
                @include('partials.delete-modal',['data' => $item,'route'=> "banks"])
              @empty
                <p>No Bank</p>
                @endforelse
            </tbody>
        </table>
        {{$bank->links()}}
    </div>
@endsection

