<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use \Illuminate\Support\Facades\DB;
use App\Notifications\Welcome;

class AuthController extends Controller
{
    //
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'guid' => \Illuminate\Support\Str::uuid(),
            'date_of_birth'=>date("Y-m-d",strtotime($request->date_of_birth))
        ]);
        $internalUser->notify(new Welcome($internalUser));
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        if(!Auth::attempt(['email' => strtolower(request('email')), 'password' => request('password')])){
            return response()->json([
                'message' => 'Login failed please try again with correct email and password.',
            ]);
        }
        else{
            $user = Auth::user();
            $tokenResult = $user->createToken('Personal Access Token')->accessToken;
            return response()->json([
                'message' => 'Logged In',
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
            ],201);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $auth = Auth::guard('api')->user();
        $user = User::where('id', $auth->id)->first();
        $request->user()->token()->revoke();
        Auth::guard('api')->logoutOtherDevices( $user->password);
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function onsuccessFullLogin(Request $request, $token){

        $access_token = $request->header('Authorization');
       
        // break up the string to get just the token
        $auth_header = explode(' ', $access_token);
    
        $tokens = $token;//$auth_header[1]?$auth_header[1]:;
        
        // break up the token into its three parts
        $token_parts = explode('.', $tokens);
       
        $token_header = $token_parts[1];
        
        // base64 decode to get a json string
        $token_header_json = base64_decode($token_header);
        
        // then convert the json to an array
        $token_header_array = json_decode($token_header_json, true);
   
        $user_token = $token_header_array['jti'];
        
        // find the user ID from the oauth access token table
        // based on the token we just got
        $user_id = DB::table('oauth_access_tokens')->where('id', $user_token)->first();
        
        // then retrieve the user from it's primary key
        $user = User::find($user_id->user_id);
        return $this->genericResponse(true, 'Login Successful',
            200, ['data' => $user,
                'token' => $token
            ]);
    }
}
