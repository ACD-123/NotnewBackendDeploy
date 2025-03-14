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
<form action="{{ route('attribute.createAttributes') }}" method="post">
    @csrf
    <input type="hidden" name="category_id" value="{{ $category->id }}"> <!-- Correctly set the category ID -->
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" name="name" value="{{ old('name', $category->name) }}" placeholder="Enter name">
    </div>
    <div class="form-group">
        <label for="relatedParent">Related Parent(s)</label>
        <div class="dropdown">
            <select name="parent_ids[]" class="form-control" id="status">
                @foreach($attributes as $attribute)
                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                @endforeach
            </select>
            <i class="fa fa-chevron-down chevron-icon" id="relatedParent-chevron"></i>
        </div>
    </div>
    <button type="submit" class="btn btn-primary form_btn">Submit</button>
</form>


</div>
@endsection
