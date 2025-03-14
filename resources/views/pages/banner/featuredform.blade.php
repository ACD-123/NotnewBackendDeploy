@extends('../layout')



@section('title','Dashboard')
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
</style>
@endpush

@section('content')
<div class="dash-set">

</div>
<div class="form-box">
    <form action="{{route('featured.banner.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="">Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>
        <div class="form-group">
            <label for="">URL</label>
            <input type="text" class="form-control" name="url" required>
        </div>
        <div class="form-group">
            <label for="">End Date</label>
            <input type="date" class="form-control" name="featured_until" required>
        </div>
        <div class="form-group">
            <label for="">GUID (Product Or Seller)</label>
            <input type="text" class="form-control" name="guid" required>
        </div>
        <div class="form-group">
            <label for="">Type (Product Or Seller)</label>
            <select name="type" id="type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="store">Store</option>
                <option value="product">Product</option>
            </select>
        </div>
        <div class="form-group">
            <label for="">21+</label>
            <select name="underage" id="underage" class="form-control" required>
                <option value="">Select Underage</option>
                <option value="1" >YES</option>
                <option value="0">NO</option>
            </select>
        </div>
        <div class="form-group">
            <label for="">Image</label>
            <input type="file" class="form-control" name="image" required>
        </div>

    
        <button type="submit" class="btn btn-primary form_btn">Submit</button>
</div>





</form>

</div>





</form>

</div>
@endsection

@push('js')

@endpush