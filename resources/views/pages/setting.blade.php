@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
    .accordion-item:first-of-type {
        border-top-left-radius: 38px;
        border-top-right-radius: 38px;
    }

    .accordion-item:last-of-type {
        border-bottom-right-radius: 38px;
        border-bottom-left-radius: 38px;
    }

    .accordion-button {
        background-color: #ffffff;
        color: #333333;
        font-weight: bold;
    }

    .accordion-button:not(.collapsed) {
        background-color: #ffff;
        color: rgba(0, 0, 0, 1);
    }

    .accordion-item {
        border: 1px solid rgba(217, 217, 217, 1);
        border-radius: 38px;
        margin-bottom: 2px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
        padding: 17px;
    }

    .accordion-body {
        font-size: 14px;
        line-height: 1.5;
        background-color: #ffffff;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .form-control {
        font-size: 14px;
    }

    .form-label {
        font-weight: bold;
        color: #333333;
    }

    .form-box {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding-top: 60px;
    }

    .form-box label {
        margin-top: 20px;
        padding-bottom: 20px;
        font-family: 'Roboto', sans-serif;
        color: rgba(117, 117, 117, 1);
        font-size: 17.96px;
        font-weight: 400;
    }

    .form_btn {
        width: unset !important;
        padding: 5px 21px !important;

    }

    .change_passdiv {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 15px;
    }

    .btn-link {
        font-size: 22px;
    }
</style>
@endpush

@section('content')
<div class="dash-set">

</div>
<div class="form-box">
    <div class="accordion" id="accordionExample">
        
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Change Password
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <form action="{{route('update.password')}}" method="POST">
                    @csrf
                        <div class="mb-3">
                            <label for="new-password" class="form-label">Create A new Password or modify a existing one</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Old Password" id="new-password">
                            @error('current_password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
                        </div>
                        <div class="mb-3">


                            <input type="password" name="new_password" class="form-control" placeholder="New Password" id="confirm-password">
                        </div>
                        <div class="change_passdiv">
                            <!-- <button type="button" class="btn btn-link">Cancel</button> -->
                            <button type="submit" class="btn btn-primary form_btn">Save</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Privacy Policy
                </button>
            </h2>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    FAQs
                </button>
            </h2>
        </div>
    </div>
</div>

@endsection

@push('js')

@endpush