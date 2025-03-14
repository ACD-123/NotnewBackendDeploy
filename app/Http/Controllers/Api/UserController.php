<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageReceived;
use App\Helpers\StripeHelper;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Mail\BaseMailable;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Models\Notification;
use App\Traits\InteractWithUpload;
use App\Notifications\DeleteAccount;
use App\Notifications\DeleteAccountUserSendEmail;
use App\Notifications\CancelDelete;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
use Storage;
use Stripe\StripeClient;
use Carbon\Carbon;
use App\Images;
use Image;
use File;
use Hash;
use Validator;
use App\Models\UserAddress;
use App\Models\WalletTransaction;

class UserController extends Controller
{
    use InteractWithUpload;
    
    public function updateUnderage(Request $request)
    {
        try{
            User::find(Auth::id())->update(["underage"=>$request->underage]);
            $user= User::find(Auth::id());
            return response()->json(['status' => 'true', 'message' => 'User Updated', 'data'=>$user], 200);
        }
        catch(\Exception $e){
            return response()->json(['status' => 'false', 'message' => $e->getMessage(),'data'=>[]], 500);
        }
    }

    public function detail()
    {
        $user = User::find(\Auth::user()->id)
            ->withMedia()
            ->withNotifications()->trusted();

        // return encrypt($user);
        return $user;
    }

    public function self()
    {
        $user = User::where('id', \Auth::user()->id)
            ->with(['savelater'])
            ->with(['media'])
            ->first();
            $user->joined=date("Y",strtotime($user->created_at));

        // return encrypt($user);
        return $user;
    }
    public function selfNew()
    {
        $user = User::where('id', \Auth::user()->id)
            ->with(['savelater'])
            ->with(['media'])
            ->first();
            $user->joined=date("Y",strtotime($user->created_at));

        // return encrypt($user);
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => "User Data!"
        ], 200);
    }

    public function detailById($id)
    {
        return User::find($id);
    }

    /**
     * @throws \Throwable
     */
    // public function upload(Request $request)
    public function upload(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user="";
            if(\Auth::check()){
                $user = User::where('id', Auth::user()->id)->first();
            }else{
                $user = User::where('id', $request->get('user_id'))->first();
            }
            $uploadData = $this->uploadImage($request, $user);
            // todo handle it in Interact with upload making a method which remove the old one and create new
            // $user = \Auth::user();

            // $hasPreviousImage = Auth::user()->getRawOriginal('profile_url');
            $hasPreviousImage = $user->getRawOriginal('profile_image');
            if (!empty($hasPreviousImage)) {
                $previous_media = $user->media()->where('type', User::MEDIA_UPLOAD)->first();
                if(File::exists($previous_media->url)) {
                    File::delete($previous_media->url);
                }
                // Storage::delete('public/' . $hasPreviousImage);
                $previous_media->delete();
            }
            $user->fill(['profile_url' => '', 'profile_image' => $uploadData['url']]);
            $user->update();
            return $user;
        });

    }

    public function conversations()
    {
        $userId = Auth::user()->id;

        return DB::select("SELECT messages.*,
                     CASE
                      WHEN sender_id!=$userId  THEN (select name from users where id = sender_id)
                      WHEN recipient_id!=$userId THEN (select name from users where id = recipient_id)
		            END as recipient_name
		          FROM
            (SELECT MAX(id) AS id
         FROM messages
         WHERE $userId IN (sender_id,recipient_id)
         GROUP BY CASE WHEN  $userId = sender_id THEN recipient_id ELSE sender_id END
         ) AS latest
        LEFT JOIN messages USING(id)
        	ORDER BY messages.updated_at desc");
    }

    public function messages(User $user)
    {
        return Message::where('sender_id', Auth::user()->id)
            ->orWhere('recipient_id', Auth::user()->id)
            // whereIn('sender_id', [Auth::user()->id, $user->id])
            // ->whereIn('recipient_id', [Auth::user()->id, $user->id])
            ->orderBy('created_at', 'asc')
            ->with(['sender' => function (BelongsTo $belongsTo) {
                $belongsTo->select(['id', 'name']);
            }])
            // ->paginate();
            ->get();
    }

    public function sendMessage(User $user, Request $request)
    {
        $message = new Message;
        $message->sender_id = Auth::user()->id;
        $message->recipient_id = $user->id;
        $message->data = $request->get('message');
        $message->save();

        // MessageReceived::trigger($user);

        return $this->genericResponse(true, 'Message sent successfully.');
    }

    //@todo Request handling
    public function update(Request $request)
    {
        if (Auth::check()) {
            // $user = User::where('id', Auth::user()->id);

            User::where('id', Auth::user()->id)->update($request->all());

            return $this->genericResponse(true, 'Profile Updated');
        }

    }

    public function profileUpdate(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = '';
            if (Auth::check()) {
                $data = [
                    "name" => $request->get('name'),
                    "last_name" => $request->get('lastname'),
                    "email" => $request->get('email'),
                    "phone" => $request->get('phone'),
                    "site" => $request->get('site'),
                    "address" => $request->get('address'),
                    "latitute"=> $request->get('latitude'),
                    "longitude"=> $request->get('longitude'),
                    "country_id" => $request->get('country'),
                    "state_id" => $request->get('states'),
                    "city_id" => $request->get('city'),
                    "zip"=> $request->get('zip'),
                ];
                $user = User::where('id', Auth::user()->id)->update($data);

               if($request->hasFile('file')){
                    $userData = User::where('id', Auth::user()->id)->first();
                    $uploadData = $this->uploadImage($request, $userData);                    
                    $updateUser = User::where('id', $userData->id)->update([
                        'profile_image' => $uploadData['url']
                    ]);
                }
            }
             $updateduser = User::where('id', Auth::user()->id)->first();
            if ($user) {
                return response()->json(['status' => 'true', 'message' => 'Profile Updated', 'data'=>$updateduser], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Unable to Update Profile!'], 500);
            }
        });
    }

    public function setSecretQuestion(Request $request)
    {
        if (Auth::check()) {
            $user = User::where('id', Auth::user()->id)->update([
                "secret_question" => $request->get('secret_question'),
                "secret_answer" => $request->get('secret_answer')
            ]);
            if ($user) {
                return response()->json(['status' => 'true', 'message' => 'Secret Question is Set.'], 200);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Secret Questions is Not Set!'], 500);
            }

            // return $this->genericResponse(true, "Secret Question Updated");
        }
    }

    public function refreshOnboardingUrl(User $user)
    {
        $accountLink = StripeHelper::createAccountLink($user);
        $user->notifications()->where('data', 'LIKE', "%$user->stripe_account_id%")->delete();

        return $accountLink->url;
    }

    public function deleteAccount($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $user->notify(new DeleteAccount($user));
            $user->notify(new DeleteAccountUserSendEmail($user));

            DB::update('update users set softdelete = ? where id = ?', [true, $id]);

            return 'Account deletion request has been sent successfully';
        } catch (\Exception $e) {
            return 'Your Request has not been Send For delete Account!. Kindly try again';
        }
    }

    public function cancelDelete($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $user->notify(new CancelDelete($user));

            return 'Your Request has been Send For Cancel delete Account!.';
        } catch (\Exception $e) {
            return 'Your Request has not been Send For delete Account!. Kindly try again';
        }
    }

    public function twoStepsVerifications(Request $request)
    {
        $twosteps = false;
        if ('1' == $request->get('twosteps')) {
            $twosteps = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            'twosteps' => $twosteps,
        ]);
        if ($user) {
            return response()->json(['status' => 'true', 'message' => '2 Factor status is Changed!'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Change 2 Factor status!'], 500);
        }

    }

    public function thirdParty(Request $request)
    {
        $thirdparty = false;
        if ('1' == $request->get('thirdparty')) {
            $thirdparty = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            'thirdparty' => $request->get('thirdparty'),
        ]);
        if ($user) {
            return response()->json(['status' => 'true', 'message' => 'Third Party App Access Changed!'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Change Third Party App Access!'], 500);
        }
    }

    public function fbAccount(Request $request)
    {
        $fbaccount = false;
        if ('1' == $request->get('fbaccount')) {
            $fbaccount = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            'fbaccount' => $fbaccount,
        ]);
        if ($user) {
            return response()->json(['status' => 'true', 'message' => 'FaceBook Status Changed!'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Change FaceBook Status!'], 500);
        }
    }
    
    
    public function updatePassword(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        if(strtolower($user->register_type)!="email"){
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'Social User can\'t change Password!'], 400);
        }
         if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'Old password is Incorrect!'], 400);
        }
          $validator = Validator::make($request->all(), [
             'old_password' => 'required',
             'password' => 'required|confirmed',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 400
            ];
            return response()->json($response, 400);
        }
        if($request->old_password==$request->password)
        {
             return response()->json(['status' => 'false', 'data' => [], 'message' => 'New password can\'t be the same as Old One!'], 400);
        }
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['status' => 'true', 'data' => [], 'message' => 'Password updated successfully!'], 200);


    }

    public function updateFCMWebToken(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        
        $validator = Validator::make($request->all(), [
             'fcm_token' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 400
            ];
            return response()->json($response, 400);
        }
            $user->fcm_web_token = $request->fcm_token;
            $user->save();

            return response()->json(['status' => 'true', 'data' => [], 'message' => 'FCM token updated successfully!'], 200);


    }
    
    public function updateNotificationSetting(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        
        $validator = Validator::make($request->all(), [
             'important_notification' => 'required',
             'chats_notification' => 'required',
             'buying_notification' => 'required',
             'selling_notification' => 'required',
             'auction_notification' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 400
            ];
            return response()->json($response, 400);
        }
            $user->important_notification = $request->important_notification;
            $user->chats_notification = $request->chats_notification;
            $user->buying_notification = $request->buying_notification;
            $user->selling_notification = $request->selling_notification;
            $user->auction_notification = $request->auction_notification;
            $user->save();

            return response()->json(['status' => 'true', 'data' => [], 'message' => 'Notification Setting updated successfully!'], 200);


    }
    public function getNotificationSetting(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        $data=[
            "important_notification"=> $user->important_notification,
            "chats_notification" =>  $user->chats_notification,
            "buying_notification" => $user->buying_notification,
           "selling_notification"=> $user->selling_notification,
            "auction_notification"=> $user->auction_notification,
        ];

            return response()->json(['status' => 'true', 'data' => $data, 'message' => 'Notification Setting!'], 200);


    }
    public function updateFCMToken(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        
        $validator = Validator::make($request->all(), [
             'fcm_token' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 400
            ];
            return response()->json($response, 400);
        }
            $user->fcm_token = $request->fcm_token;
            $user->save();

            return response()->json(['status' => 'true', 'data' => [], 'message' => 'FCM token updated successfully!'], 200);


    }
    
     public function getUserAddress(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        $address=UserAddress::with('user')->where('user_id',$user->id)->get();

            return response()->json(['status' => 'true', 'data' => $address, 'message' => 'User Address!'], 200);


    }
    
    public function storeUserAddress(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        UserAddress::create([
        "city"=>$request->city,
        "country"=>$request->country,
        "state"=>$request->state,
        "zip"=>$request->zip,
        "address"=>$request->address,
        "latitude"=>$request->latitude??"0.0",
        "longitude"=>$request->longitude??"0.0",
        "user_id"=>$user->id,
        "label"=>$request->label??$request->city." ".$request->country,
        "street"=>$request->street
        ]);
            return response()->json(['status' => 'true', 'data' => [], 'message' => 'User Address Stored!'], 200);


    }
    
    
       public function updateUserAddress(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        $address=UserAddress::find($request->id);
         if (!$address) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No Address Found!'], 400);
        }
        $address->update([
        "city"=>$request->city,
        "country"=>$request->country,
        "state"=>$request->state,
        "zip"=>$request->zip,
        "address"=>$request->address,
        "latitude"=>$request->latitude??"0.0",
        "longitude"=>$request->longitude??"0.0",
        "user_id"=>$user->id,
        "label"=>$request->label??$request->city." ".$request->country,
        "street"=>$request->street
        ]);
            return response()->json(['status' => 'true', 'data' => [], 'message' => 'User Address Updated!'], 200);


    }
    
       public function deleteUserAddress(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No User Found!'], 400);
        }
        $address=UserAddress::find($request->id);
         if (!$address) {
            return response()->json(['status' => 'false', 'data' => [], 'message' => 'No Address Found!'], 400);
        }
        $address->delete();
         return response()->json(['status' => 'true', 'data' => [], 'message' => 'User Address Deleted!'], 200);
    }
    public function logoutFCMToken(Request $request)
    {
       $user = Auth::user();
        if (!$user) {
            return customApiResponse(false, [], 'No User Found!', 400);
        }
        
        $validator = Validator::make($request->all(), [
             'type' => 'required'
        ]);
        $type=$request->type;
        if ($validator->fails()) {
            return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
        }
        if($type=="web"){
            $user->fcm_web_token = NULL;
            $user->save();
            return customApiResponse(true, [], 'FCM token updated successfully!');
        }
        if($type=="mobile"){
            $user->fcm_token = NULL;
            $user->save();
            return customApiResponse(true, [], 'FCM token updated successfully!');
        }
    }
    public function walletTransaction(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return customApiResponse(false, [], 'Log In First!', 400);
            }
            $total = WalletTransaction::where('user_id', '=', $user->id)->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages = ceil($total / $page_size);
            $pagination = [
                'total' => $total,
                'page' => $page,
                'page_size' => $page_size,
                'total_pages' => $total_pages,
                'remaining' => $total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $walletTransactions = WalletTransaction::where('user_id', '=', $user->id)->skip($skip)->take($page_size)->orderBy('created_at','DESC')->get();
            return customApiResponse(true, ["transactions" => $walletTransactions, "pagination" => $pagination], 'Transactions!');
        } catch (Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Error', 500);
        }
    }
    public function walletAmount(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return customApiResponse(false, [], 'Log In First!', 400);
            }
            return customApiResponse(true, ["amount" => $user->wallet], 'Amount!');
        } catch (Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Error', 500);
        }
    }


    public function updateAddress(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->update([
            'address' => $request->get('address'),
            'city_id' => $request->get('city_id'),
            'country_id' => $request->get('country_id'),
            'latitute' => $request->get('latitute'),
            'longitude' => $request->get('longitude'),
            'state_id' => $request->get('state_id'),
        ]);
        if ($user) {
            return response()->json(['status' => 'true', 'message' => 'Address Created Successuly!'], 200);
        } else {
            return response()->json(['status' => 'false', 'message' => 'Unable to Save Address Created Successuly!'], 500);
        }
    }
}
