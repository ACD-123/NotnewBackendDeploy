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


class UserController extends Controller
{
    use InteractWithUpload;

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
        $user = User::where('id',\Auth::user()->id)
        ->with(['savelater'])
        ->with(['media'])
        ->first();
        // return encrypt($user);
        return $user;
    }

    public function detailById($id)
    {
        return User::find($id);
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function upload(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $uploadData = $this->uploadImage($request, Auth::user());
            /// todo handle it in Interact with upload making a method which remove the old one and create new
            $user = \Auth::user();

            $hasPreviousImage = Auth::user()->getRawOriginal('profile_url');

            if (!empty($hasPreviousImage)) {
                $previous_media = Auth::user()->media()->where('type', User::MEDIA_UPLOAD)->first();

                Storage::delete('public/' . $hasPreviousImage);
                $previous_media->delete();
            }
            
            $user->fill(['profile_url' => $uploadData['absolute_path']]);
            $user->save();
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
        return Message::
             where('sender_id', Auth::user()->id)
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
        $message = new Message();
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

            return $this->genericResponse(true, "Profile Updated");
        }

    }
    public function profileUpdate(Request $request)
    {
        return DB::transaction(function () use ($request) {
        if (Auth::check()) {
                $data =[
                    "email" => $request->get('email'),
                    "phone" => $request->get('phone'),
                    "site" => $request->get('site'),
                    "address" => $request->get('address'),
                ];

                $user = User::where('id', Auth::user()->id)->update($data);
              
                if($request->hasFile('file')){
                    $user = User::orderBy('id', 'desc')->first();
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();    
                    $guid = GuidHelper::getGuid();
                    $path = User::getUploadPath($user->id) . StringHelper::trimLower(Media::USER);
                    $name = "{$path}/{$guid}.{$extension}";  
                    // $name = $file->getClientOriginalName();
                    // array_push($imageName, $name);

                    $userMedia = Media::where('user_id',$user->id)
                        ->where('type','user')
                        ->first();
                    if($userMedia){
                        Storage::delete($userMedia->url);
                        // Storage::disk('public')->delete($userMedia->url);
                    }
                    Media::where('user_id',$user->id)
                        ->where('type','user')
                        ->delete();
                    $media = new Media();
                    $media->fill([
                        'name' => $name,
                        'extension' => $extension,
                        'type' => Media::USER,
                        'user_id' => $user->id,
                        'active' => true,
                    ]);
                    $media->save();
                    $updateCoverImage = User::where('id',$user->id)->update([
                        'profile_image' => $name
                    ]);
                    // $image = Image::make($file)->save(public_path('image/product/') . $name);
                    $image = Image::make($file);
                        $image->orientate();
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                    });
                        $image->stream();
                        Storage::put('/'. $name, $image->encode());
                }
            }
            return $this->genericResponse(true, "Profile Updated");
        });
    }
    public function setSecretQuestion(Request $request){
        if (Auth::check()) {
            $user = User::where('id', Auth::user()->id)->update([
                "secret_question" => $request->get('secret_question')
            ]);
            if($user){
                return response()->json(['status'=>'true','message'=>'Secret Question is Set.'],200);
            }else{
                return response()->json(['status'=>'false','message'=>'Secret Questions is Not Set!'],500);
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
        try{
            $user = User::where('id',$id)->first();
            $user->notify(new DeleteAccount($user));
            $user->notify(new DeleteAccountUserSendEmail($user));
            
            DB::update('update users set softdelete = ? where id = ?',[true,$id]);
            return "Account deletion request has been sent successfully";
        }
        catch(\Exception $e){ 
            return 'Your Request has not been Send For delete Account!. Kindly try again';
        }       
    }
    public function cancelDelete($id)
    {
        try{
            $user = User::where('id',$id)->first();
            $user->notify(new CancelDelete($user));
            
            return "Your Request has been Send For Cancel delete Account!.";
        }
        catch(\Exception $e){ 
            return 'Your Request has not been Send For delete Account!. Kindly try again';
        }       
    }

    public function twoStepsVerifications(Request $request)
    {
        $twosteps = false; 
        if($request->get('twosteps') == "1"){
            $twosteps = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            "twosteps" => $twosteps
        ]);
        if($user){
            return response()->json(['status'=>'true','message'=>'2 Factor status is Changed!'],200);
        }else{
            return response()->json(['status'=>'false','message'=>'Unable to Change 2 Factor status!'],500);
        }

    }

    public function thirdParty(Request $request)
    {
        $thirdparty = false; 
        if($request->get('thirdparty') == "1"){
            $thirdparty = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            "thirdparty" => $request->get('thirdparty')
        ]);
        if($user){
            return response()->json(['status'=>'true','message'=>'Third Party App Access Changed!'],200);
        }else{
            return response()->json(['status'=>'false','message'=>'Unable to Change Third Party App Access!'],500);
        }
    }

    public function fbAccount(Request $request)
    {
        $fbaccount = false; 
        if($request->get('fbaccount') == "1"){
            $fbaccount = true;
        }
        $user = User::where('id', Auth::user()->id)->update([
            "fbaccount" => $fbaccount
        ]);
        if($user){
            return response()->json(['status'=>'true','message'=>'FaceBook Status Changed!'],200);
        }else{
            return response()->json(['status'=>'false','message'=>'Unable to Change FaceBook Status!'],500);
        }
    }

    public function updateAddress(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->update([
            "address" => $request->get('address'),
            "city_id" => $request->get('city_id'),
            "country_id" => $request->get('country_id'),
            "latitute" => $request->get('latitute'),
            "longitude" => $request->get('longitude'),
            "state_id" => $request->get('state_id')
        ]);
        if($user){
            return response()->json(['status'=>'true','message'=>'Address Created Successuly!'],200);
        }else{
            return response()->json(['status'=>'false','message'=>'Unable to Save Address Created Successuly!'],500);
        }
    }

}
