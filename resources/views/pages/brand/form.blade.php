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
    <form action="{{route('brands.store')}}" method="post">
        @csrf
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Enter name">
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