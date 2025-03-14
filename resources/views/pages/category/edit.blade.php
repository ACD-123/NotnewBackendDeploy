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
    <form action="{{route('category.update',$category->id)}}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">

        @csrf
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" value="{{$category->name}}" class="form-control" name="name" placeholder="Enter name">
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <div class="dropdown">
                <select class="form-control" name="type" id="type">
                <option value="Product" {{$category->type == 'Product' ? 'selected' : ''}}>Product</option>
                <option value="Service" {{$category->type == 'Service' ? 'selected' : ''}}>Service</option>
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
                    <option value="1" {{$category->underage == 1 ? 'selected' : ''}}>Yes</option>
                    <option value="0" {{$category->underage == 0 ? 'selected' : ''}}>No</option>
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="age-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <div class="dropdown">
                <select name="active" class="form-control" id="status">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$category->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$category->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="status-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="relatedParent">Related Parent</label>
            <div class="dropdown">
                <select name="parent_id" class="form-control" id="relatedParent">
                    <option value="" selected>Please select...</option>
                    @foreach($categories as $cat)
                        <option {{$category->parent_id == $cat->id ? 'selected':''}}
                                value={{$cat->id}}>{{$cat->name}}</option>
                    @endforeach
                </select>
                <i class="fa fa-chevron-down chevron-icon" id="relatedParent-chevron"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="">Related Parent</label>
            <input type="file" name="file"  class="form-control" name="image">
            @foreach($category->media as $media)<img src="{{url('/image/category/')}}/{{$media->name}}" alt="{{ $media->name }}" width="100" height="100" /> @endforeach

        </div>

        <div class="form-group">
            <label for="">Description</label>
            <textarea name="description"  placeholder="Write something here.." class="form-control" id="" cols="20" rows="7">{{$category->description}}</textarea>
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