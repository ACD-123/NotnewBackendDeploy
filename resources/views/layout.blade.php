
<!DOCTYPE html>
<html lang="en">
@include('templete.head')
   
<body>
@include('templete.header')
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    
                    @include('templete.sidebar')
                </div>
                <div class="col-md-9">
            @yield('content')


        </div>
              
            </div>
            
        </div>
    </div>
    @include('templete.footer')
</body>


</html>