<?php


namespace App\Http\Controllers\Api\Auth;
use App\Models\User;
use App\Models\Notifications;
use App\Models\Otp;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgetPasswordVerification;
use App\Notifications\SuccessfullRegister;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Helpers\StringHelper;
use Illuminate\Http\Response;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function check(Request $request){
        $user = User::whereRaw('lower(email) = ?', [$request->get('email')])->first(); 
        if ($user) {
            if(strtolower($user->register_type)!="email"){
                return response()->json(['status'=>'false','message'=>'Social User can\'t change Password! Go to Login Screen!'],400);
                //return return response()->json(["status"=>false, "message"=>'Social User can\'t change Password! Go to Login Screen!',"data"=>[]],400);
            }
            Otp::where('email','=',strtolower($user->email))->delete();
            Notification::send($user, new ForgetPasswordVerification());
         }else{
            // return $this->genericResponse(false, 'Email not found',500);
            // return Response::json(['message' => 'Email not found'],400);
            return response()->json(['status'=>'false','message'=>'Email not found'],400);
            // throw ValidationException::withMessages(['email' => trans($user)]);
         }
         return $this->genericResponse(true, 'Otp Send SuccessFully',200);
    }

    public function checkUI(Request $request){
        $user = User::whereRaw('lower(email) = ?', [$request->get('email')])->first(); 
        if ($user) {
            if(strtolower($user->register_type)!="email"){
                return response()->json(['status'=>'false','message'=>'Social User can\'t change Password! Go to Login Screen!'],400);
                //return return response()->json(["status"=>false, "message"=>'Social User can\'t change Password! Go to Login Screen!',"data"=>[]],400);
            }
            Otp::where('email','=',strtolower($user->email))->delete();
            Notification::send($user, new ForgetPasswordVerification());
         }else{
            // return $this->genericResponse(false, 'Email not found',500);
            // return Response::json(['message' => 'Email not found'],400);
            return response()->json(['status'=>'false','message'=>'Email not found'],400);
            // throw ValidationException::withMessages(['email' => trans($user)]);
         }
         $request->session()->put('email',$request->email);
         
         return redirect()->route('forget-code');
        //  return $this->genericResponse(true, 'Otp Send SuccessFully',200);
    }
    public function verifyOtp(Request $request){

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            throw ValidationException::withMessages(['message' => "add All Feilds"]);
        }
        if (Otp::where('otp', $request->otp)->count() != 1) {
            throw ValidationException::withMessages(['message' => "Otp Is Incorrect"]);
        } 
        Otp::where('otp', $request->otp)->delete();
        return $this->genericResponse(200, 'You can change your password.');
     
    }
    public function verifyOtpUI(Request $request){
        $validator = $this->validator($request->all());
    
        if ($validator->fails()) {
            throw ValidationException::withMessages(['message' => "Add all fields"]);
        } else if (Otp::where('otp', $request->otp)->count() != 1) {
            throw ValidationException::withMessages(['message' => "OTP is incorrect"]);
        } 
       
        Otp::where('otp', $request->otp)->delete();
        return redirect()->route('forget-password');
    }
    
    public function verifyAuthOtp(Request $request){
        return DB::transaction(function () use ($request) {  
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                return [
                    'status'  =>  'Fail',
                    'message' => "Required All Feilds"
                ];
                // throw ValidationException::withMessages(['message' => "add All Feilds"]);
            }
            if (Otp::where('otp', $request->otp)->count() != 1) {
                return response()->json(['status'=>'false','message'=>'Otp Not Verified!'],400);
                // return [
                //     'status'  =>  'Fail',
                //     'message' => "Otp Not Verified!"
                // ];
                // thow ValidationException::withMessages(['message' => "Otp Is Incorrect or You may already Registered!"]);
            }else{
                $opt = Otp::where('otp', $request->otp)->first();
                $user = User::where('email', $opt->email)->first();
                User::where('email', $opt->email)->update([
                    'email_verified_at' => Carbon::now()->toDateTimeString()
                ]);
                
                Notification::send($user, new SuccessfullRegister());
                $notification = new Notifications();
                $notification->id = StringHelper::createKey();
                $notification->type = "Successfull Register";
                $notification->notifiable_type = "Register";
                $notification->notifiable_id = $user->id;
                $notification->data = "You have Successfully Registered With Not New..";
                $notification->save();

                Otp::where('otp', $request->otp)->delete();
                return $this->genericResponse(200, 'Otp Verified Successfully!.');
            } 
        });
    }
    protected function validator(array $data)
    {
        return  Validator::make($data,[
            'otp' => 'required',
        ]);
    }
    protected function changePassword(Request $request){ 
        $user = User::where('id',\Auth::user()->id)->update([
            "password" => Hash::make($request->get('confirm_password'))
        ]);
        if($user){
            return response()->json(['status'=>'true','message'=>'Password is Changed'],200);
        }else{
            return response()->json(['status'=>'false','message'=>'Password is not Changed!'],400);
        }

    }
    public function resendForgetOtp(Request $request){
        try{
            $user =User::where('email', $request->get('email'))->first();
            $checkOtp = Otp::where('email', $request->get('email'))
            ->where('otp_type', 'ForgetPassword')
            ->first();
            if($checkOtp){
                Otp::where('email', $request->get('email'))
                ->where('otp_type', 'ForgetPassword')
                ->delete();
            }
            $user->notify(new ForgetPasswordVerification($user));
            return response()->json(['status'=>'true','data'=>"OTP has been Resend!!"],200);
        }
        catch(Exception $e) {
            return response()->json(['status'=>'false','data'=>$e],500);
        }
    }
}
