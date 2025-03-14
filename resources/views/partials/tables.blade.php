<div class="container">
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-8">
            <p>From Customer <strong>{{$customer->name}}</strong></p>
        </div>
        <div class="col-md-4 text-right">
            <form action="{{route("{$route}.search")}}" method="GET">
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
            <th scope="col">{{$name}} Name</th>
            <th scope="col">Status</th>
            <th scope="col">Hot Daily Deals</th>
            <th scope="col">Price</th>
            {{--<th scope="col">Category</th>--}}
            {{--<th scope="col">Created At</th>--}}
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        @php
            $count = 1;
        @endphp
        @foreach($data as $item)
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
                  <td>
                    <button type="button"
                            class="{{$item->hot  == 1 ? "btn btn-success" : "btn btn-danger"}}"
                            data-toggle="modal" data-target="#productshot{{$item->id}}">
                        {{$item->hot == 1 ? 'Yes' : 'No'}}
                    </button>
                </td>
                <td>$ {{$item->price}}</td>
                {{--                    <td>{{$item->category->name}}</td>--}}
                {{--                    <td>{{$item->created_at}}</td>--}}
                <td>
                    <a href="{{route("{$route}.edit", $item->id)}}" class="btn btn-info"><i class="fa fa-pen"></i></a>
                    <form action="{{ route("{$route}.destroy", $item->id) }}" method="POST" style="display: unset">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                        style="color: white"></i></button>
                    </form>
                </td>
            </tr>
            @include('partials.status-modal',['data' => $item, 'route' => $route])
            @include('partials.hotmodal',['data' => $item, 'route' => "products.hot"])
            
        @endforeach
        </tbody>
    </table>
    {{--        {{$customerProduct->links()}}--}}
</div>

