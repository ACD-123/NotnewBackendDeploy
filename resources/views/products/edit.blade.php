@extends('adminlte::page')

@section('content')
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('products.update',$product->id)}}" method="POST">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input readonly type="text" name="name" class="form-control" placeholder="Enter Product Name"
                       value="{{$product->name}}">
            </div>
            @if($product->selling_now == 1)
            <div class="form-group">
                <label>Price</label>
                <input readonly type="number" step="0.00" min="0" name="price" class="form-control"
                       placeholder="Enter Product Price $" value="{{$product->price}}" required>
            </div>
            @elseif($product->auctioned == 1)
            <div class="form-group">
                <label>Bids</label>
                <input readonly type="number" step="0.00" min="0" name="price" class="form-control"
                       placeholder="Enter Product Price $" value="{{$product->bids}}" required>
            </div>
            @endif
            <div class="form-group">
                <label>Category</label>
                <select readonly id="categories" name="category_id" class="form-control" onchange="onCategorySelect(this)">
                    @foreach($category as $item)
                        <option  @if($product->category->id == $item->id) selected  @endif value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>
            <div id="attributes-div">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$product->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$product->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea readonly type="text" rows="5" cols="5" class="form-control" name="description"
                          placeholder="Enter Product Description">{{$product->description}}</textarea>
            </div>
            @IF(count($media))
                <div class="form-group">
                    <label>Media</label>
                    <div>
                        @foreach($media as $item)
                            <img style="width: 100px; height: 100px;" src="{{$item->name}}" alt="{{$item->guid}}"/>
                        @endforeach
                    </div>
                </div>
            @ENDIF
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
    document.addEventListener('DOMContentLoaded', function () {
        onCategorySelect(document.getElementById('categories'));
    })

    function onCategorySelect(elem) {
        if (elem.value !== '') {
            $('#attributes-div').load(`/admin/category/${elem.value}/attributes/{{$product->id}}`);
        }
    }
</script>
