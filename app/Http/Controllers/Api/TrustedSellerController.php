<?php

namespace App\Http\Controllers\Api;


use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Models\TrustedSeller;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TrustedSellerNotification;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;


class TrustedSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = Auth::guard('api')->id();
        $notifiable = User::where('id', $user_id)->first();
       
        $user = TrustedSeller::create([
            'name' => $request->get("name"),
            'email' => $request->get("email"),
            'address' => $request->get("address"),
            'number' => $request->get("number"),
            'store' => $request->get("store"),
            'facebook' => $request->get("facebook"),
            'instagram' => $request->get("instagram"),
            'ein' => $request->get("ein"),
            'ssn' => $request->get("ssn"),
            'businessType' => $request->get("businessType"),
            'website' => $request->get("website"),
            'user_id' => $user_id,
            'courriertype' => $request->get("courrierType"),
            'shipmenttype' => $request->get("shipementtype"),
            'price' => $request->get("price"),
            'days' => $request->get("days")
        ])->get();
        
        User::where('id', $user_id)->update(['isTrustedSeller'=>true]);

        $notifiable->notify(new TrustedSellerNotification($user));

        return $this->genericResponse(true, 'Your form has been submitted.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return TrustedSeller::where('user_id', $id)->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_id = Auth::guard('api')->id();
        $user=TrustedSeller::where('user_id',$user_id)->get();
        $notifiable = User::where('id', $user_id)->first();
        $users = TrustedSeller::where('id', $id)->update([
            'name' => $request->get("name"),
            'email' => $request->get("email"),
            'address' => $request->get("address"),
            'number' => $request->get("number"),
            'store' => $request->get("store"),
            'facebook' => $request->get("facebook"),
            'instagram' => $request->get("instagram"),
            'ein' => $request->get("ein"),
            'ssn' => $request->get("ssn"),
            'businessType' => $request->get("businessType"),
            'website' => $request->get("website"),
            'user_id' => $user_id,
            'courriertype' => $request->get("courrierType"),
            'shipmenttype' => $request->get("shipementtype"),
            'price' => $request->get("price"),
            'days' => $request->get("days")
        ]);
        $notifiable->notify(new TrustedSellerNotification($user));

        return $this->genericResponse(true, 'Updated.');
    }
    public function getUploadFile(Request $request){
        $TrustedSeller = TrustedSeller::where('user_id', \Auth::user()->id)->first();
        if($TrustedSeller){
            return Media::where('user_id', \Auth::user()->id)->get();
        }else{
            return null;
        }
    }
    public function uploadFile(User $user, Request $request)
    {
        return DB::transaction(function () use (&$request, &$user) {
            $file =$request->file('file');
            $extension = $file->getClientOriginalExtension();
            if($extension == "pdf" || $extension == "doc" || $extension == "docx"){
                $guid = GuidHelper::getGuid();
                $path = User::getUploadPath() . StringHelper::trimLower(Media::TRUSTEDSELLER_FILES);
                $name = "{$path}/{$guid}.{$extension}";
                $media = new Media();
                $media->fill([
                    'name' => $name,
                    'extension' => $extension,
                    'type' => Media::TRUSTEDSELLER_FILES,
                    'user_id' => \Auth::user()->id,
                    // 'product_id' => $product->id,
                    'active' => true,
                ]);

                $media->save();
                
                Storage::putFileAs(
                    'public/' . $path,
                    $request->file('file'),
                    "{$guid}.{$extension}"
                );

                return [
                    'uid' => $media->id,
                    'name' => $media->url,
                    'status' => 'done',
                    'url' => $media->url,
                ];
            }else{
                return [
                    'error' =>  'You can only upload Doc and Pdf file!',
                ];
            }
           
        });
    }
    public function get($id){
        return TrustedSeller::where('user_id',$id)->first();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
