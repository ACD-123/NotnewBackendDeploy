@extends('adminlte::page')

@section('content')

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{route('attribute.update',$attribute->id)}}" method="POST">
            <input type="hidden" name="_method" value="PUT">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{$attribute->name}}" class="form-control"
                       placeholder="Enter Category Name" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select class="form-control" name="type" placeholder="Select Type" onchange="onTypeSelect(this.value)">
                    @foreach(\App\Models\Attribute::types() as $value => $text)
                        <option
                            value="{{$value}}" {{$attribute->type === $value ? 'selected' : null}}>{{$text}}</option>
                    @endforeach
                </select>
            </div>
            <div id="options-div" class="form-group" style="display: none">
                <label>Options</label>
                <select id="options" class="form-control" name="options[]" multiple="multiple">
                    @if($attribute->options)
                        @foreach($attribute->options as $option)
                            <option value="{{$option}}" selected>{{$option}}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control" required>
                    <option value="" selected>Please select...</option>
                    <option value="1" {{$attribute->active == 1 ? 'selected' : ''}}>Active</option>
                    <option value="0" {{$attribute->active == 0 ? 'selected': ''}}>In-Active</option>
                </select>
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
    document.addEventListener('DOMContentLoaded', function () {
        $('#options').select2({tags: true});
        onTypeSelect($('[name=type]').val());
    });

    function onTypeSelect(type) {
        if (<?= json_encode(\App\Models\Attribute::typesWithOptions()) ?>.includes(type)) {
            $('#options-div').show();
        } else {
            $('#options-div').hide();
        }
    }
</script>
