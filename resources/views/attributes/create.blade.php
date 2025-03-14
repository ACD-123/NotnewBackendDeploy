@extends('adminlte::page')

@section('content')
    <div class="container">
        <form action="{{route('attribute.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter Attribute Name">
            </div>

            <div class="form-group">
                <label>Type</label>
                <select class="form-control" name="type" placeholder="Select Type" onchange="onTypeSelect(this.value)">
                    @foreach(\App\Models\Attribute::types() as $value => $text)
                        <option value="{{$value}}">{{$text}}</option>
                    @endforeach
                </select>
            </div>

            <div id="options-div" class="form-group" style="display: none">
                <label>Options</label>
                <select id="options" class="form-control" name="options[]" multiple="multiple"
                        placeholder="Add Options">
                </select>
            </div>


            <div class="form-group">
                <label>Status</label>
                <select name="active" class="form-control">
                    <option value="" selected>Please select...</option>
                    <option value=1>Active</option>
                    <option value=0>In-Active</option>
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
    });

    function onTypeSelect(type) {
        if (<?= json_encode(\App\Models\Attribute::typesWithOptions()) ?>.includes(type)) {
            $('#options-div').show();
        } else {
            $('#options-div').hide();
        }
    }
</script>
