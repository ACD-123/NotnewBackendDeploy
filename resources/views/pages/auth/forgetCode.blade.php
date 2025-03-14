<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- links -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{url('/public/css/styleLogin.css')}}"> 
    <title>Forget Code</title>
</head>
<body>
<div class="main-forget-sec">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="logo">
                    <img src="public/imagesLogin/logo.png" alt="">

                    <h1>Password Recovery</h1>
                </div>
            </div>
            
                <div class=" offset-md-1 col-md-5">
                    <div class="form-forget">
                        <div class="text-code-sec">
                            <h1>Forgot Password</h1>
                            <div style="border:3px solid #FFFF; border-radius: 10px; margin: 0 auto;width: 60%;"></div>
                            <p>Now enter your 4 digit code we’ve sent you on the email <span style="color: #6CACBB;">{{ session('email') }}</span></p>
                            
                        </div>
                       
                         <!-- <form action="{{route('forget-code')}}" method="POST">
                            @csrf
                          <div class="form-group otp_btn">
                            <input type="text" id="otp1" name="code" class="otp-input" maxlength="4" autofocus>
                            <input type="text" id="otp2" class="otp-input" maxlength="1">
                            <input type="text" id="otp3" class="otp-input" maxlength="1">
                            <input type="text" id="otp4" class="otp-input" maxlength="1">
                          </div>
                         
                          <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary form_btn">Send Code</button>
                            <a href="forget-password" class="forgot-password">Forgot Password?</a>

                          </div>

                            

                        
                        </form> -->

                        <form id="otp-form" action="{{ route('forget-code') }}" method="POST">
    @csrf
    <div class="form-group otp_btn">
        <input type="text" id="otp1" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp2')" autofocus>
        <input type="text" id="otp2" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp3')">
        <input type="text" id="otp3" class="otp-input" maxlength="1" oninput="moveToNext(this, 'otp4')">
        <input type="text" id="otp4" class="otp-input" maxlength="1" oninput="combineOtp()">
        
        <!-- Hidden input to store the combined OTP -->
        <input type="hidden" id="otp-combined" name="otp">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary form_btn">Send Code</button>
    </div>
</form>

                    </div>
                
            </div>
            
        </div>
    </div>
</div>    


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // JavaScript to handle focus shifting between inputs
    document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length == 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            if (e.target.value.length == 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    function moveToNext(current, nextFieldId) {
    if (current.value.length >= 1) {
        document.getElementById(nextFieldId).focus();
    }
}

function combineOtp() {
    // Combine the values of the four OTP input fields
    let otp1 = document.getElementById('otp1').value;
    let otp2 = document.getElementById('otp2').value;
    let otp3 = document.getElementById('otp3').value;
    let otp4 = document.getElementById('otp4').value;

    let combinedOtp = otp1 + otp2 + otp3 + otp4;

    // Set the combined OTP in the hidden input field
    document.getElementById('otp-combined').value = combinedOtp;
}

</script>

</body>
</html>