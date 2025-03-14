<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\GuidHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Facebook\Facebook;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTAuth;
use App\Notifications\Welcome;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;
    protected $redirectTo = '';
    protected $auth;

    /**
     * LoginController constructor.
     * @param JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
//        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @todo right now simple JWT TOKEN after move to passport soon
     */
    public function login(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        //     // 'remember_me' => 'boolean'
        // ]);
         $validator = Validator::make($request->all(), [
           'email' => 'required|email',
            //   'name' => 'required|string|max:50',
           'password' => 'required|string'
        ]);
        
       if ($validator->fails()) {
           return response()->json(['status'=>'false','message'=> $validator->errors()->first()],403);
       }
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return response()->json(['errors' => "you've been locked",'status'=>'false','message'=>'You attempt number of time your account has been blocked!'],403);

            // return $this->genericResponse(false, 'You attempt number of time your account has been blocked',
            //     null, ['errors' => [
            //         "you've been locked"
            //     ]]);
        }
        $checkuser = User::where('email', strtolower(request('email')))->first();
        if(!$checkuser){
            // return $this->genericResponse(false, 'Invalid Email', 403, [
            //     'status' => 'fail',
            //     'message' => 'Email not Exits!'
            // ]);
            return response()->json(['status'=>'false','message'=>'Email not Exits!'],403);
        }else{
            if (!Auth::attempt(['email' => strtolower(request('email')), 'password' => request('password')])) {
                return response()->json(['status'=>'false','message'=>trans('auth.failed')],403);
                // return $this->genericResponse(false, 'Login Failed', 403, [
                //     'status' => 'fail',
                //     'message' => [trans('auth.failed')]
                // ]);
                // throw ValidationException::withMessages([
                //     $this->username() => [trans('auth.failed')],
                // ]);
            }
            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);
            $user = Auth::user();
            $user->validateEmailVerification();
            if(request('fcm_token')){
                $user->device_token = request('fcm_token');
                $user->update();
            }
            $tokenResult = $user->createToken('Personal Access Token')->accessToken;
            /**
             * encrypting and decrypting starts
             */
                // $encrypted = encrypt('my plain text'); 
                // echo $encrypted;
                // $decrypted = decrypt($encrypted); 
                // echo $decrypted;
            /**
             * encrypting and decrypting ends
             */
            if(request('remember_me') == 1){
                return $this->genericResponse(true, 'Login Successful',
                200, [
                    'data' => encrypt($request->user()),
                    'token' => $tokenResult,
                    'user'=>$user,
                    'rememberme' => true
                ]);
            }
            else{
                return $this->genericResponse(true, 'Login Successful',
                200, [
                    'token' => $tokenResult,
                    'user'=>$user
                ]);
            }
        }
    }

    private function unProcessEntityResponse($message = '')
    {
        return $this->genericResponse(false, $message,
            422, ['errors' => [
                'email' => 'Invalid address or password',
            ]]);
    }

    public function facebookLogin(Request $request)
    {

        $fb = new Facebook([
            'app_id' => config('app.facebook.app_id'),
            'app_secret' => config('app.facebook.app_secret'),
            'default_graph_version' => 'v8.0',
        ]);
      
        $response = $fb->get('/me?fields=id,name,email,picture', $request->get('accessToken'));
      
        $fbUser = $response->getGraphUser();
        $internalUser = User::where('email', $fbUser->getEmail())->first();
        if ($internalUser === null) {
            $internalUser = new User([
                'name' => $fbUser->getName(),
                'email' => $fbUser->getEmail()? $fbUser->getEmail() : "no-email@facebook.com",
                'password' => Hash::make(Str::random(8)),
                'guid' => GuidHelper::getGuid(),
                'email_verified_at' => Carbon::now()
            ]);
            $internalUser->save();
            $internalUser->notify(new SocialWelcome($internalUser));
        }
        Auth::login($internalUser);

        return $this->genericResponse(true, 'Login Successful', 200, [
            'data' => $request->user(),
            'token' => $internalUser->createToken('Personal Access Token')->accessToken
        ]);
    }

    public function googleLogin(Request $request)
    {
        $client = new \Google_Client(['client_id' => config('app.google.client_id')]);
        $googleUser = $client->verifyIdToken($request->get('credential'));
        // $valid = $client->verifyIdToken("eyJhbGciOiJSUzI1NiIsImtpZCI6IjdjMGI2OTEzZmUxMzgyMGEzMzMzOTlhY2U0MjZlNzA1MzVhOWEwYmYiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhenAiOiI1NjQ5MzI1NjQ1MzEtYjl1Y2hrdmZsZGozdTFkcnQwZnZmM2w0ZTZjZThodTEuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20iLCJhdWQiOiI1NjQ5MzI1NjQ1MzEtYjl1Y2hrdmZsZGozdTFkcnQwZnZmM2w0ZTZjZThodTEuYXBwcy5nb29nbGV1c2VyY29udGVudC5jb20iLCJzdWIiOiIxMDExNTI1MjI2NTk2Nzg0MzQ2NTgiLCJlbWFpbCI6InJhamFhc3NhZDMyQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYmYiOjE2OTQ2MTQyOTYsIm5hbWUiOiJBc3NhZCBSYWphIiwicGljdHVyZSI6Imh0dHBzOi8vbGgzLmdvb2dsZXVzZXJjb250ZW50LmNvbS9hL0FDZzhvY0lYUzg0TjMybG92UVVxR3piZ2xMbjBtdUZZMnN3NUlZeVNOeTFGdmV3YT1zOTYtYyIsImdpdmVuX25hbWUiOiJBc3NhZCIsImZhbWlseV9uYW1lIjoiUmFqYSIsImxvY2FsZSI6ImVuLUdCIiwiaWF0IjoxNjk0NjE0NTk2LCJleHAiOjE2OTQ2MTgxOTYsImp0aSI6ImRhZWMyZmUwNjAyOGNlYzhlMzZhOTBiMDk0YTYzOTkzODRmOWY5MmUifQ.M_SZh6amtBxyXrKDYLHETjlXJvTVU7_8e9N01x3MJc0_vYX2n3uC34x4hdaR7qYCeb1C_hykE27CMbnmMAJ53otmBrHU5ycCBOwxKycc97aEwfL7L8R4tL4UBW4tmKx-mN2IXtcdbOvIMmux4KZTkhv6mHwZ083gM-yymvgrpMsHPmq5nLyWGnLZ71BKW3GlGciPra1vJQVIcyVAzEZcLy0I2_I6GgTHPeJXDKG_-hSOYa9nwYIJ2e3vYWn13HV7KF9PYGlupsARM3QdMwmQITbcvpUzCREU1KhcjCAfXHhWXK66DMn0cNLjPTZq0Lxxrru6RhjDcF2245YbINnHKQ");
       
        if ($googleUser || $request->has('is_mobile')) {
            // $googleUser = $request->get('user');
            $internalUser = User::where('email', $googleUser['email'])->first();
            if ($internalUser === null) {
                $internalUser = new User(array_merge(
                    $googleUser,
                    [
                        'password' => Hash::make(Str::random(8)),
                        'guid' => GuidHelper::getGuid(),
                        'email_verified_at' => Carbon::now()
                    ]
                ));
                $internalUser->save();
                $internalUser->notify(new SocialWelcome($internalUser));
            }
            Auth::login($internalUser);

            $user = auth()->check() ? auth()->user() : $request->user();

            return $this->genericResponse(true, 'Login Successful', 200, [
                'data' => $user,
                'token' => $internalUser->createToken('Personal Access Token')->accessToken
            ]);
        }

        throw ValidationException::withMessages(['token' => 'Invalid token provided.']);
    }

    public function appleLogin(Request $request)
    {
        $identityToken = $request->get('identityToken');
        $authorizationCode = $request->get('authorizationCode');
        $appleUser = $request->get('user');

        if (true) {
            $internalUser = User::where('email', $appleUser['email'])->first();
            if ($internalUser === null) {
                $internalUser = new User(array_merge(
                    $appleUser,
                    [
                        'password' => Hash::make(Str::random(8)),
                        'guid' => GuidHelper::getGuid(),
                        'email_verified_at' => Carbon::now()
                    ]
                ));
                $internalUser->save();
                $internalUser->notify(new Welcome($internalUser));
            }
            Auth::login($internalUser);

            $user = auth()->check() ? auth()->user() : $request->user();

            return $this->genericResponse(true, 'Login Successful', 200, [
                'data' => $user,
                'token' => $internalUser->createToken('Personal Access Token')->accessToken
            ]);
        }

        throw ValidationException::withMessages(['token' => 'Invalid token provided.']);
    }
}
