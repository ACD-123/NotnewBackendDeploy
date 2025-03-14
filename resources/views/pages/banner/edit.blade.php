@extends('layout')



@section('title', 'Edit Attribute')

@push('css')
<style>
    .form-control {
        padding: 25px;
        border-radius: 26px;
    }

    .form-box input.form-control {
        margin-bottom: 0;
    }

    label {
        color: #495057;
    }

    .chevron-icon {
        font-size: 20px;
        position: absolute;
        right: 24px;
        top: 30px;
    }

    .dropdown {
        position: relative;
    }

    .form_btn {
        padding: 11px 20px;
        width: unset !important;
    }

    .attribute-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        padding: 5px;
        background-color: #f1f1f1;
        border-radius: 4px;
    }

    .cancel-icon {
        cursor: pointer;
        color: red;
        margin-left: 10px;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="dash-set">
</div>
<div class="form-box">
    <form action="{{ route('banner.update', $banner->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT') 
       
       
        <div class="form-group">
            <label for="">Image</label>
            <input type="file" class="form-control" name="image">
        </div>

        <div class="form-group">
            <label for="">21+</label>
            <select name="underage" id="underage" class="form-control" required>
                <option value="">Select Underage</option>
                <option value="1" {{$banner->underage==1?"selected":""}}>YES</option>
                <option value="0" {{$banner->underage==0?"selected":""}}>NO</option>
            </select>
        </div>

       

        <button type="submit" class="btn btn-primary form_btn">Update</button>
    </form>
</div>
@endsection
