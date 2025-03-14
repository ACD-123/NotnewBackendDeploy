@extends('../layout')



@section('title','Categories')

@push('css')
<style>
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
        top: 95% !important;
        left: 30% !important;
        right: auto;
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

    .date-selector {}

    .icon-container img {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }

    .date-selector span {}

    .dropdown-icon {
        font-size: 12px;
        color: #6e6e6e;
    }

    /* date css */
    /* dates */

    .date-selector {}

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
<div class="dash-set">
    <h3>Edit Category</h3>
</div>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('category.update',$category->id)}}" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Name</label>
                <input type="text" name="name" value="{{$category->name}}" class="form-control"
                       placeholder="Enter Category Name" required>
            </div>

            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Type</label>
                <select name="type" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="Product" {{$category->type == 'Product' ? 'selected' : ''}}>Product</option>
                    <option value="Service" {{$category->type == 'Service' ? 'selected' : ''}}>Service</option>
                </select>
            </div>
            
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">21+</label>
                <select name="underage" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$category->underage == 1 ? 'selected' : ''}}>Yes</option>
                    <option value="0" {{$category->underage == 0 ? 'selected' : ''}}>No</option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Status</label>
                <select name="active" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$category->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$category->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Related Parent</label>

                <select name="parent_id" class="form-control">
                    <option value="" selected>Please select...</option>
                    @foreach($categories as $cat)
                        <option {{$category->parent_id == $cat->id ? 'selected':''}}
                                value={{$cat->id}}>{{$cat->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Related Image</label>
                <input type="file" require name="file" class="form-control">
                @foreach($category->media as $media)<img style="margin-top:20px" src="{{url('/image/category/')}}/{{$media->name}}" alt="{{ $media->name }}" width="100" height="100" /> @endforeach
            </div>
            <div class="form-group mb-2">
                <label class="mb-2" style="font-weight: bold;">Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Description">{{$category->description}}</textarea>
            </div>
            <button type="submit" class="btn form_btn" style="padding:10px 40px !important;font-size:16px !important;margin-top:20px">Submit</button>
        </form>
        <br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

@endsection
