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
    <title>Reset Password</title>
</head>
<body>
<div class="main-forget-sec">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="logo">
                    <img src="public/imagesLogin/logo.png" alt="">

                    <h1>Reset Password</h1>
                </div>
            </div>
            
                <div class=" offset-md-1 col-md-5">
                    <div class="form-forget">
                        <div class="text-code-sec">
                            <h1>Reset Password</h1>
                            <div style="border:3px solid #FFFF; border-radius: 10px; margin: 0 auto;width: 60%;"></div>
                            <p>Enter your new password and make sure its we secured</p>
                            
                        </div>
                        <form action="{{ route('forget-password') }}" method="POST">
    @csrf
    <div class="form-group">
        <input type="password" placeholder="Enter new password" required class="form-control" name="password">
    </div>
    <div class="form-group">
        <input type="password" placeholder="Confirm new password" required class="form-control" name="password_confirmation">
    </div>
    <div class="form-group">
        <button type="submit" name="submit" class="btn btn-primary form_btn">Change Password</button>
    </div>
</form>

                    </div>
                
            </div>
            
        </div>
    </div>
</div>    


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


</body>
</html>
