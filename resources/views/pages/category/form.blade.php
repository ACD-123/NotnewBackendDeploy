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
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Enter name">
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <div class="dropdown">
                <select class="form-control" name="type" id="type">
                    <option value="" selected>Please select...</option>
                    <option value="Product">Product</option>
                    <!-- Add more options here -->
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="type-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="age">21+</label>
            <div class="dropdown">
                <select name="underage" class="form-control" id="age">
                    <option value="" selected>Please select...</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="age-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <div class="dropdown">
                <select name="active" class="form-control" id="status">
                    <option value="" selected>Please select...</option>
                    <option value="1">Active</option>
                    <option value="0">In-Active</option>
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="status-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="relatedParent">Related Parent</label>
            <div class="dropdown">
                <select name="parent_id" class="form-control" id="relatedParent">
                    <option value="" selected>Please select...</option>
                    @foreach($categories as $category)
                        <option value={{$category->id}}>{{$category->name}}</option>
                    @endforeach
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="relatedParent-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="">Related Parent</label>
            <input type="file" name="file" class="form-control" name="image">
        </div>

        <div class="form-group">
            <label for="">Description</label>
            <textarea name="description" placeholder="Write something here.." class="form-control" id="" cols="20" rows="7"></textarea>
        </div>


        <button type="submit" class="btn btn-primary form_btn">Submit</button>
</div>





</form>

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