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
                <a href="{{route('products.create')}}" class="btn btn-primary">Add New</a>
                <form id="form-submit" role="form" action="{{route('products.active-all')}}" method="POST">
                    {{ csrf_field()}}
                    <button class="btn btn-success"><i class="fa fa-key"></i> Activate
                        All Products
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <form action="{{route('products.inactive.search')}}" method="GET">
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
                <th scope="col">Status</th>
                <th scope="col">Price</th>
                <th scope="col">Created By</th>
                {{--<th scope="col">Category</th>--}}

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
                    <td>{{$item->products->name}}</td>
                    <td>
                        <button type="button"
                                class="{{$item->products->active  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                                data-toggle="modal" data-target="#products{{$item->products->id}}">
                            {{$item->products->active == 1 ? 'Active' : 'Un-Active'}}
                        </button>
                    </td>
                    <td>$ {{$item->products->price}}</td>
                    <td>
                        <a href="{{route('customer.products',$item->products->user->id)}}">{{$item->products->user->name}}</a>
                    </td>
                    {{--<td>{{$item->category->name}}</td>--}}
                    {{--<td>{{$item->created_at}}</td>--}}
                    <td>
                        <a href="{{route('products.edit', $item->products->id)}}" class="btn btn-info"><i
                                class="fa fa-pen"></i></a>

                        <form action="{{ route('products.destroy', $item->products->id) }}" method="POST"
                              id="form-submit">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form>
                    </td>
                </tr>
        {{--Modal --}}
                @include('partials.status-modal',['data' => $item->products,'route'=> "products"])
            @empty
                <p>No In-Active Products</p>
            @endforelse
            </tbody>
        </table>
        {{--{{$products->links()}}--}}
    </div>
@endsection
{{--public css was not rendering properly this is the reason why i put this here--}}
<style>
    #form-submit {
        display: unset;
    }
</style>
