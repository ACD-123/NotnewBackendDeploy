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
      <link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css">
    <title>Login</title>
</head>
<body>
<div class="main-sec">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="logo">
                    <img src="public/imagesLogin/logo.png" alt="">

                    <h1>welcome Admin</h1>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged</p>
                </div>
            </div>
            
                <div class=" offset-md-1 col-md-5">
                    <div class="form-sec">
                         <form action="{{route('admin.login')}}" method="post">
                          @csrf
                          <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" placeholder="YOU123@gmail.com" required class="form-control" name="email">
                          </div>
                          <div class="form-group">
                            <label for="">password</label>
                            <input type="password" id="password-field" placeholder="*************"  required class="form-control" name="password">
                            <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                            @error('login_error')
                <span class="text-danger">{{ $message }}</span>
            @enderror
                          </div>
                          <div class="for-rem">
                            <div class="form-group">
                                <input type="checkbox" id="remember-me" name="remember-me">
                                <label for="remember-me">Remember Me</label>
                              </div>
                              <div class="form-group">
                                <a href="forget-email" class="forgot-password">Forgot Password?</a>
                              </div>
                          </div>
                          <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary form_btn" >Login</button>
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
   $(".toggle-password").click(function() {

$(this).toggleClass("fa-eye fa-eye-slash");
var input = $($(this).attr("toggle"));
if (input.attr("type") == "password") {
  input.attr("type", "text");
} else {
  input.attr("type", "password");
}
});

</script>

</body>
</html>