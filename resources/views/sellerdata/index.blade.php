@extends('adminlte::page')

@section('content')

    <div class="container">

        <h3 class="text-center mb-5">Seller Shop</h3>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            {{--
                <!-- <div class="col-md-8">
                    <a href="{{route('sellerdata.create')}}" class="btn btn-primary">Add New</a>
                </div> -->
            --}}
            <div class="col-md-4 text-right">
                <form action="{{route('sellerdata.search')}}" method="GET">
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
                <th scope="col">Seller</th>
                <th scope="col">Status</th>
                <th scope="col">Featured</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($sellerdata as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->fullname}}</td>
                    <td><a href="{{route('customer.products',$item->user->id)}}">{{$item->user->name}}</a></td>
                    <td>@if($item->active == 1)
                            Active
                        @elseif($item->active == 0)
                            InActive
                        @endif    
                    </td>
                    <td>@if($item->featured == 1)
                          Featured
                        @elseif($item->featured == 0)
                          Non Featured
                        @endif    
                    </td>
                    <td>
                        <a href="{{route('sellerdata.edit', $item->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>
                                <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i></button>
                                <a href="{{route('sellerdata.report',['guid'=>$item->guid])}}" class="btn btn-danger"><i
                                class="fa fa-ban"></i></a>
                    </td>
                </tr>   
                @include('partials.delete-modal',['data' => $item,'route'=> "sellerdata"])
              @empty
                <p>No Seller Shop</p>
                @endforelse
            </tbody>
        </table>
        {{$sellerdata->links()}}
    </div>
@endsection

