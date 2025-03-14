@extends('adminlte::page')

@section('content')
    <h3 class="text-center mb-5">Trusted Seller</h3>

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
                <th scope="col">Status</th>
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
                    <td><span
                            class="{{$item->isTrustedSeller == 0 ? 'badge badge-danger' : 'badge badge-success'}}">{{$item->isTrustedSeller == 0 ? 'IN-ACTIVE' : 'ACTIVE' }}</span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            @if($item->percentage)
                                <form action="{{route('trusted-seller.edit', $item->user_id)}}" method="POST">
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                @if($item->isTrustedSeller == 1)
                                    <input type="hidden" name="status" value="false"  />
                                @elseif($item->isTrustedSeller == 0)
                                    <input type="hidden" name="status" value="true"  />
                                @endif
                                    <button class="btn btn-info" type="submit">{{ $item->isTrustedSeller == 0 ? 'Active' : 'In Active'}}</button>
                                </form>
                            @endif
                            <a style="margin-left: 3px" href="{{route('trusted-seller.show', $item->id)}}" class="btn btn-info"><i
                                        class="fa fa-pen"></i></a>
                    </div>
                    </td>
                </tr>   
              @empty
                <p>No In Active Users</p>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection