@extends('../layout')



@section('title','Dashboard')
@push('css')
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
        /* Ensure the input field is properly styled or hidden */
        display: none;
    }

    .flatpickr-calendar {
        z-index: 1000;
        /* Ensure the datepicker appears above other elements */
    }

    .flatpickr-calendar.animate.open.arrowTop.arrowLeft {
        top: 85% !important;
        left: 25% !important;
        right: auto;
    }

    span.relative.z-0.inline-flex.shadow-sm.rounded-md {
        display: flex;
        align-items: center;
    }

    span.relative.z-0.inline-flex.shadow-sm.rounded-md svg.w-5.h-5 {
        width: 25px;
    }

    span.relative.z-0.inline-flex.shadow-sm.rounded-md a {
        height: 50px;
    }

    relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5 span.relative.inline-flex.items-center.px-4.py-2.-ml-px.text-sm.font-medium.text-gray-500.bg-white.border.border-gray-300.cursor-default.leading-5 {
        height: 50px;
    }

    .d-flex.justify-content-center.pagination {}

    .d-flex.justify-content-center.pagination span {}

    span.relative.inline-flex.items-center.px-2 {}

    span.relative.inline-flex.items-center.px-2.py-2.text-sm.font-medium.text-gray-500.bg-white.border.border-gray-300.cursor-default.rounded-l-md.leading-5 {}

    .tabs {
        padding-top: 50px !important;
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
    <a href="category-form" class="btn form_btn">
        <i class="fas fa-upload"></i> Upload new Category
    </a>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="tables" style="padding-bottom:40px;">
            <div class="table-responsive" id="all">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category as $categories)
                        <tr>
                            <td>{{$categories->id}}</td>
                            <td>
                                @foreach($categories->media as $media)
                                <img src="image/category/{{ $media->name }}" height="100" alt="">
                                @endforeach
                            </td>
                            <td>{{$categories->name}}</td>
                            <td>{{\Str::limit($categories->description,20)}}</td>
                            <td><span class="status-active">{{$categories->active}}</span></td>
                            <td><span class="type-product">{{$categories->type}}</span></td>
                            <td>{{$categories->created_at}}</td>
                            <td>
                                <a href="{{url('/categoryEdit',$categories->id)}}" class="btn btn-edit"><i class="fas fa-pen"></i></a>
                                <a href="{{url('/admin/category/attributes',$categories->id)}}" class="btn btn-edit-attr"></i>Edit Attribute</a>

                                <form action="{{ route('category.destroy', $categories->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach



                    </tbody>

                   



                </table>

               
            </div>


        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pagingType": "full_numbers",
            "lengthMenu": [10, 25, 50, 75, 100],
            "pageLength": 10
        });
    });
</script>
@endpush