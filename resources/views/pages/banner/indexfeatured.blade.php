@extends('../layout')



@section('title','Dashboard')
@push('css')


<style>
    /* data table */
    /* Custom Styles */
    .dataTables_wrapper {
        font-size: 14px;
    }

    .dataTables_wrapper td img {
        width: 50px;
        height: 50px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 8px 16px;
        margin: 0 2px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: rgba(0, 195, 201, 1);
        color: white;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        margin-bottom: 10px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_info {
        margin-top: 10px;
    }

    .btn {
        padding: 4px 8px;
        border-radius: 4px;
        color: white;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
    }

    .btn-edit {
        background-color: rgba(0, 195, 201, 1);
        font-family: 'Roboto', sans-serif;
    }

    .btn-delete {
        background-color: rgba(239, 42, 42, 1);
        font-family: 'Roboto', sans-serif;
    }

    .status-active {
        background-color: rgba(102, 234, 28, 1);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        text-align: center;
        font-family: 'Roboto', sans-serif;
    }

    .type-product {
        background-color: rgba(26, 140, 233, 1);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        text-align: center;
        font-family: 'Roboto', sans-serif;
    }

    .btn-edit-attr {
        background-color: rgba(0, 195, 201, 1);
    }
</style>
<style>
    .tabs {
        padding-top: 50px !important;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 335px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        position: absolute;
        top: 10px;
        left: 20px;
        font-size: 14px;
        color: #666;
    }

    .chart-subtitle {
        position: absolute;
        top: 30px;
        left: 20px;
        font-size: 24px;
        color: #333;
        font-weight: bold;
    }

    .chart-dropdown {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 14px;
        color: #333;
        cursor: pointer;
    }


    .header-title {
        font-size: 24px;
        color: #333;
        padding-bottom: 5px;
        margin-top: 20px;
    }

    .table-responsive {
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;

    }

    .custom-table thead {
        background-color: #ffff;
        border: 1px solid rgba(139, 44, 160, 1);
        vertical-align: middle;
    }

    .custom-table thead th {
        /* border-bottom: 2px solid #ddd; */
        padding: 10px;
        color: #555;
        font-weight: bold;
        text-align: center;
    }

    .custom-table tbody td {
        padding: 15px;
        vertical-align: middle;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .table-row:hover {
        background-color: #f1f9ff;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }

    .profile-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .payment-icon {
        width: 50px;
        object-fit: contain;
    }

    .badge {
        font-size: 14px;
        padding: 5px 12px;
        border-radius: 12px;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: #fff;
    }

    .date-selector {
        display: inline-flex;
        align-items: center;
        padding: 16px 12px;
        border-radius: 20px;
        border: 1px solid #e0e0e0;
        background-color: #ffffff;
        font-family: Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        margin-bottom: 22px;

    }

    .icon-container img {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }

    .date-selector span {
        margin-right: 8px;
        color: #000;
    }

    .dropdown-icon {
        font-size: 12px;
        color: #6e6e6e;
    }

    /* date css */
    /* dates */

    .date-selector {
        position: relative;
        display: inline-block;
    }

    .icon-container {
        display: inline-block;
    }

    #displaydate {
        display: inline-block;
        margin-left: 5px;
    }

    .dropdown-icon {
        display: inline-block;
        cursor: pointer;
        margin-left: 5px;
    }

    #calendar {

        display: none;
    }

    .flatpickr-calendar {
        z-index: 1000;
        /* Ensure the datepicker appears above other elements */
    }

    .flatpickr-calendar.animate.open.arrowTop.arrowLeft {
        top: 41% !important;
        left: 30% !important;
        right: auto;
    }

    .btn-delete {
        padding: 4px 8px;
        border-radius: 4px;
        background: linear-gradient(to top, rgba(139, 44, 160, 1), rgba(3, 191, 200, 1));
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
        color: white;
        /* Default text color */
    }

    a:hover {
        color: white !important;
    }



    .form_btn {
        padding: 10px 8px !important;
        font-size: 13.51px !important;
        width: unset !important;
        background: linear-gradient(to top, #00C3C9, #8B2CA0);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .button-calen {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }
</style>
@endpush

@section('content')


<div class="button-calen">
    <div class="date-selector" style="visibility: hidden;">
        
    </div>
    <input type="hidden" id="date">
    <a href="featured-banner-form" class="btn form_btn">
        <i class="fas fa-upload"></i> Upload new Featured Banners
    </a>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="tables" style="padding-bottom:40px;">
            <!-- Orders Table -->
         

            <table class="table table-borderless custom-table">
            <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th>End Date</th>
                        <th>21+</th>
                        <th>Image</th>
                        <th>Type</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <!-- Repeat these rows as necessary -->
                    @foreach($banners as $banner)
                    <tr>
                        <td>{{$banner->id}}</td>
                        <td>{{$banner->title}}</td>
                        <td>{{$banner->url}}</td>
                        <td>{{$banner->featured_until}}</td>
                        <td>{{$banner->underage==1?"YES":"NO"}}</td>
                        <td><img src="image/category/{{$banner->media[0]->name}}" style="width:100px;"></td>
                        <td>{{$banner->type}}</td>
                        <td>
                            <a href="{{url('/featured-banner-edit',$banner->id)}}" class="btn btn-edit"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('featured.banner.destroy', $banner->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                </tbody>





            </table>

        </div>
    </div>
</div>

@endsection

@push('js')

@endpush