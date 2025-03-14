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
    <form action="{{route('banner.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="">Image</label>
            <input type="file" class="form-control" name="image" required>
        </div>

        <div class="form-group">
            <label for="">21+</label>
            <select name="underage" id="underage" class="form-control" required>
                <option value="">Select Underage</option>
                <option value="1" >YES</option>
                <option value="0">NO</option>
            </select>
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