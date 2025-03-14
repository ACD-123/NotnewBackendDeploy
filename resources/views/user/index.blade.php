@extends('adminlte::page')

@section('content')
    <h3 class="text-center mb-5">Users</h3>

    <div class="container">
    @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
    @endif
    <table class="table">
            <br>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Flexe Fee</th>
                <th scope="col">Status</th>
                <th scope="col">Auto Active</th>
                <th scope="col">Cancel Status</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($user as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->email}}</td>
                    <td>{{$item->percentage?$item->percentage:0}}</td>
                    <td><span
                            class="{{$item->status == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$item->status == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span>
                    </td>
                    <td>{{ $item->is_autoAdd == 1 ? 'Active' : 'Not Active'}}</td>
                    <td>{{ $item->softdelete == 1 ? 'Requested' : 'Not Requested'}}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            @if($item->percentage)
                                <form action="{{route('user.edit', $item->id)}}" method="POST">
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                @if($item->isTrustedSeller == 1)
                                    <input type="hidden" name="status" value="false"  />
                                @elseif($item->isTrustedSeller == 0)
                                    <input type="hidden" name="status" value="true"  />
                                @endif
                                    
                                </form>
                            @endif
                            <a style="margin-left: 3px" href="{{route('user.show', $item->id)}}" class="btn btn-info"><i
                                        class="fa fa-pen"></i></a>
                                        <button type="button"
                                class="btn btn-danger"
                                data-toggle="modal" data-target="#products1{{$item->id}}">
                                <i class="fa fa-trash" style="color: white"></i>
                        </button>
                        <!-- <form action="{{ route('user.destroy', $item) }}" method="POST"
                              style="display: unset">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-danger" type="submit"><i class="fa fa-trash"
                                                                            style="color: white"></i></button>
                        </form> -->
                    </div>
                    </td>
                </tr>   
                @include('partials.delete-modal',['data' => $item,'route'=> "user"])
              @empty
                <p>No In Active Users</p>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection