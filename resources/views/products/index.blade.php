@extends('adminlte::page')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            {{--<!-- <div class="col-md-8">
                <a href="{{route('products.create')}}" class="btn btn-primary">Add New</a>
            </div> -->--}}
            <div class="col-md-4 text-right">
                <form action="{{route('products.search')}}" class="form-anticlear" method="GET">
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
                <th scope="col">Product Name</th>
                <th scope="col">Product Type</th>
                <th scope="col">Status</th>
                <th scope="col">Price/Bids</th>
                <th scope="col">Hot Daily Deals</th> 
                <th scope="col">Created By</th>
                {{--<th scope="col">Category</th>--}}
                {{--<th scope="col">Created At</th>--}}
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($products as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>
                        @if($item->selling_now == 1)
                         <p>Sell it now</p>   
                        @elseif($item->auctioned == 1)
                         <p>Auctioned</p>
                        @endif
                    </td>
                    <td>
                        <button type="button"
                                class="{{$item->active  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                                data-toggle="modal" data-target="#products{{$item->id}}">
                            {{$item->active == 1 ? 'Active' : 'In-Active'}}
                        </button>
                    </td>
                    <td>
                        @if($item->selling_now == 1)
                        $ {{$item->price}}
                        @elseif($item->auctioned == 1)
                        $ {{$item->bids}}
                        @endif
                    </td>
                    <td>
                        <button type="button"
                                class="{{$item->hot  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                                data-toggle="modal" data-target="#productshot{{$item->id}}">
                            {{$item->hot == 1 ? 'Yes' : 'No'}}
                        </button>
                    </td>
                    <td>
                       <a href="{{route('customer.products',$item->user->id)}}">{{$item->user->name}}</a>
                    </td>
                    {{--<td>{{$item->category->name}}</td>--}}
                    {{--<td>{{$item->created_at}}</td>--}}
                    <td>
                    <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i>
                        </button>
                        <a href="{{route('products.edit', $item->id)}}" class="btn btn-info"><i
                                    class="fa fa-pen"></i></a>
                        <!-- <form action="{{ route('products.destroy', $item->id) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" onclick="return confirm('Are you sure?')" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                    </td>
                </tr>
                <!-- Modal -->
                @include('partials.status-modal',['data' => $item,'route'=> "products"])
                @include('partials.hotmodal',['data' => $item,'route'=> "products.hot"])
                @include('partials.delete-modal',['data' => $item,'route'=> "products"])
            @empty
                <p>No Active Products</p>
            @endforelse
            </tbody>
        </table>
        {{$products->links()}}
    </div>
    <script src="https://cdn.jsdelivr.net/gh/akjpro/form-anticlear/base.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script> -->
    <!-- <script>
        $("#myform").submit(function(e) { e.preventDefault(); })
    </script> -->
@endsection
