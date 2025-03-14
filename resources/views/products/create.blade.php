@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('products.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter Product Name">
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.00" min="0" name="price" class="form-control"
                       placeholder="Enter Product Price $" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control" onchange="onCategorySelect(this)">
                    <option value="" selected>Please select...</option>
                    @foreach($categories as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>
            <div id="attributes-div">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1">Active</option>
                    <option value="0">In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Product Description">
            </textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
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

<script type="application/javascript">
    function onCategorySelect(elem) {
        if (elem.value !== '') {
            $('#attributes-div').load(`/admin/category/${elem.value}/attributes`);
        }
    }
</script>
