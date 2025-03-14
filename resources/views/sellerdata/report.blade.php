@extends('adminlte::page')

@section('content')

    <div class="container">

        <h3 class="text-center mb-5">Seller Shop Report Request</h3>

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
                <th scope="col">User Name</th>
                <th scope="col">Name</th>
                <th scope="col">Seller</th>
                <th scope="col">Reason</th>
                <th scope="col">Message</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 1;
            @endphp
            @forelse($reports as $item)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$item->user->name}}</td>
                    <td><a href="{{route('customer.products',$item->user->id)}}">{{$sellerData->user->name}}</a></td>
                    <td>{{$sellerData->fullname}}   
                    </td>
                    <td>{{$item->reason}}  
                    </td>
                    <td>{{$item->message}}  
                    </td>
                </tr>   
                
              @empty
                <p>No Report</p>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

