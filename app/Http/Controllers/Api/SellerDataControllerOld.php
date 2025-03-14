<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use Illuminate\Http\Request;
use App\Models\SellerData;
use App\Models\UserOrderDetails;
use App\Models\User;
use App\Models\Media;
use App\Models\UserOrder;
use App\Models\Product;
use App\Models\Feedback;
use App\Models\UserBank;
use App\Models\SaveSeller;
use App\Models\Bank;
use App\Http\Requests\SellerDataRequest;
use App\Notifications\SellerDataNotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\InteractWithUpload;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Images;
use Image;
use File;

class SellerDataController extends Controller
{
    use InteractWithUpload;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SellerData::get();
    }

    public function getBank()
    {
        return Bank::get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        
        return DB::transaction(function () use ($request) {
            $checkUser = SellerData::where('user_id', Auth::user()->id)->first();
            if($checkUser)
            {
                return response()->json(['status'=> false,'data' =>"Your store is being verified by the admin!"], 409);       
            }else{
                $checkseller = SellerData::where('email', $request->email)->first();

            if($checkseller){
                return response()->json(['status'=> false,'data' =>"Email Already Exist"], 409);       
            }else{
                SellerData::where('user_id', \Auth::user()->id)->delete();
                // $checkseller = SellerData::where('user_id', \Auth::user()->id)->first();
                // if($checkseller){
                //     return $this->genericResponse(false, "Store Already Exists!", 400);
                // }else{
                    // if($request->hasFile('file')){
                    //     $user = User::where('id', Auth::user()->id)->first();
                    //     $file = $request->file('file');
                    //     $extension = $file->getClientOriginalExtension();    
                    //     $guid = GuidHelper::getGuid();
                    //     $path = User::getUploadPath($user->id) . StringHelper::trimLower(Media::STORE);
                    //     $name = "{$path}/{$guid}.{$extension}";  
                    //     // $name = $file->getClientOriginalName();
                    //     // array_push($imageName, $name);
                    //     $media = new Media();
                    //     $media->fill([
                    //         'name' => $name,
                    //         'extension' => $extension,
                    //         'type' => Media::STORE,
                    //         'user_id' => $user->id,
                    //         'active' => true,
                    //     ]);
                
                    //     $media->save();
                    //     // $image = Image::make($file)->save(public_path('image/product/') . $name);
                    //     $image = Image::make($file);
                    //         $image->orientate();
                    //         $image->resize(1024, null, function ($constraint) {
                    //             $constraint->aspectRatio();
                    //             $constraint->upsize();
                    //     });
                        
                    //     $image->stream();
                    //     Storage::put('/'. $name, $image->encode());
                    // }
                        $sellerData = new SellerData();
                        $sellerData->user_id = \Auth::user()->id;
                        $sellerData->country_id = $request->country;
                        $sellerData->state_id = $request->state;
                        $sellerData->city_id = $request->city;
                        $sellerData->fullname = $request->fullname;
                        $sellerData->email = $request->email;
                        $sellerData->phone = $request->phone;
                        $sellerData->address = $request->address;
                        $sellerData->zip = $request->zip;
                        $sellerData->description = $request->description;
                        // $sellerData->password = $request->password;
                        // $sellerData->password_confirmation = $request->password_confirmation;
                        $sellerData->guid = GuidHelper::getGuid();
                        $sellerData->save();
                        if($request->hasFile('file')){
                
                            $uploadData = $this->uploadImage($request, $sellerData);

                            SellerData::where('id',$sellerData->id)
                                ->update(['cover_image' => $uploadData['url']]);
                        }
                        
                        $selldata =  SellerData::where('id',$sellerData->id)->first();
                        $user = User::where('id', \Auth::user()->id)->first();
                        //$user->notify(new SellerDataNotify($user));
                        // return $this->genericResponse(true, "Seller Register Successfully!", 200);
                        if($selldata){
                            return response()->json(['status'=> true,'data' =>"Seller Created SuccessFully"], 200);       
                            // return response()->json(['status'=> true,'data' =>$selldata], 200);       
                        }else{
                            return response()->json(['status'=> false,'data' =>"Unable To Get Seller Data"], 400);       
                        }
    
            }   
            }
        }); 
        // return DB::transaction(function () use ($request) {
        //     $sellerData = new SellerData();
        //     $sellerData->user_id = \Auth::user()->id;
        //     $sellerData->country_id = $request->country;
        //     $sellerData->state_id = $request->state;
        //     $sellerData->city_id = $request->city;
        //     $sellerData->fullname = $request->name;
        //     $sellerData->email = $request->email;
        //     $sellerData->phone = $request->phone;
        //     $sellerData->address = $request->address;
        //     $sellerData->zip = $request->zip;
        //     // $sellerData->password = $request->password;
        //     // $sellerData->password_confirmation = $request->password_confirmation;
        //     $sellerData->guid = GuidHelper::getGuid();
        //     $sellerData->save();
        //     $user = User::where('id', \Auth::user()->id)->first();
        //     User::where('id', \Auth::user()->id)->update([
        //         "isTrustedSeller" => true
        //     ]);
        //     // $user->notify(new SellerDataNotify($user));
        //     return "You have SuccessFully Registered as Seller in NotNew";
        // });
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      //
    }

    public function getShopDetails()
    {
        $seller = SellerData::with('feedback')->where('user_id', \Auth::user()->id)->first();
        if($seller){
            return response()->json(['status'=> true,'data' =>$seller], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Seller"], 400);       
        }
    }
    
    public function getShopDetail($id)
    {
        // $userId = \Auth::user()->id? \Auth::user()->id: $id;
        
        $seller = SellerData::with('feedback')->where('guid', $id)->first();
            $orderdetails = UserOrderDetails::where("store_id", $seller->id)->get();
            $orderids = [];
            foreach($orderdetails as $orderdetail){
                array_push($orderids, $orderdetail->order_id);    
            }
            $orderids_ = array_unique($orderids);
            $orderids_values = array_values($orderids_);
            $itemsolds = count($orderids_values);
            $items = Product::where('shop_id', $seller->id)->get();
            $shopItems =[];
            foreach($items as $item){
                $shopItem =[
                    "image" => $item->media[0],
                    "name"  => $item->name,
                    "price" => $item->price,
                    "saleprice" => "41.77",
                    "off" => "50 % OFF",
                    "shop_id" =>$item->shop_id
                ];    
                array_push($shopItems, $shopItem);
            }
            $date = new \DateTime($seller->created_at);
            // $o = new \ReflectionObject($date);
            // $p = $o->getProperty('date');
            // $dat = $p->getValue($dt);
           //return $date);
           //die();
            $about =[
                "video" => "https://www.youtube.com/embed/tgbNymZ7vqY",
                "aboutus" => $seller->description,
                "name" => $seller->fullname,
                "joined" => $date,
                "location" => $seller->city_id .' - '.$seller->country_id
                ];
            
            $data=[
                "main_image" => "https://notnewbackend.testingwebsitelink.com/image/category/1712418909-collectibles.jpg",
                "cover_image" => "https://notnewbackend.testingwebsitelink.com/".$seller->cover_image,
                "name" => $seller->fullname,
                "positive_feedback" => "90",
                "itemsold" => $itemsolds,
                "shop" => $shopItems,
                "about" =>$about
            ];

        if($seller){
            return response()->json(['status'=> true,'data' => $data], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
        }
        
    }
    public function getShopDetailProduct($id)
    {
        // $userId = \Auth::user()->id? \Auth::user()->id: $id;
        //For debugging
        $seller = SellerData::where('guid', $id)->first();

        $products = Product::where('shop_id', $seller->id)->get();
        if($products){
            return response()->json(['status'=> true,'data' =>$products], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
        }
        
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    public function updateSellerData(Request $request)
    {
        // return DB::transaction(function () use ($request) {
        //         $name="";
        //         // if($request->hasFile('file')){
        //         //     $user = User::where('id', Auth::user()->id)->first();
        //         //     $file = $request->file('file');
        //         //     $extension = $file->getClientOriginalExtension();    
        //         //     $guid = GuidHelper::getGuid();
        //         //     $path = User::getUploadPath($user->id) . StringHelper::trimLower(Media::STORE);
        //         //     $name = "{$path}/{$guid}.{$extension}";  
        //         //     // $name = $file->getClientOriginalName();
        //         //     // array_push($imageName, $name);
        //         //     $media = new Media();
        //         //     $media->fill([
        //         //         'name' => $name,
        //         //         'extension' => $extension,
        //         //         'type' => Media::STORE,
        //         //         'user_id' => $user->id,
        //         //         'active' => true,
        //         //     ]);
            
        //         //     $media->save();
        //         //     // $image = Image::make($file)->save(public_path('image/product/') . $name);
        //         //     $image = Image::make($file);
        //         //         $image->orientate();
        //         //         $image->resize(1024, null, function ($constraint) {
        //         //             $constraint->aspectRatio();
        //         //             $constraint->upsize();
        //         //     });
                    
        //         //     $image->stream();
        //         //     Storage::put('/'. $name, $image->encode());
        //         // }
        //         $sellData = SellerData::where('guid', $request->get('guid'))->first();
        //         $hasPreviousImage = $sellData->getRawOriginal('cover_image');
        //         $coverImage= $hasPreviousImage;
        //         if($request->hasFile('file')){
                
        //             if (!empty($hasPreviousImage)) {
        //                 $previous_media = Auth::user()->media()->where('type', SellerData::MEDIA_UPLOAD)->first();
        //                 if($previous_media){
        //                     if(File::exists($previous_media->url)) {
        //                         File::delete($previous_media->url);
        //                     }
        //                     // Storage::delete('public/' . $hasPreviousImage);
        //                     $previous_media->delete();
        //                 }
        //             }
        //             $uploadData = $this->uploadImage($request, $sellData);
        //             $coverImage= $uploadData['url'];
        //         }
        //         $sellerData = SellerData::where('guid', $request->get('guid'))
        //         ->update([
        //             // 'user_id' => $request->user_id,
        //             'country_id' => $request->country,
        //             'state_id' => $request->state,
        //             'city_id' => $request->city,
        //             'fullname' => $request->fullname,
        //             'email' => $request->email,
        //             'phone' => $request->phone,
        //             'address' => $request->address,
        //             'zip' => $request->zip,
        //             'cover_image' => $coverImage
        //         ]);                     
                    
        //         return $this->genericResponse(true, "You have SuccessFully Update Shop Data!", 200);
        // }); 
        return DB::transaction(function () use ($request) {
                $name="";
                // if($request->hasFile('file')){
                //     $user = User::where('id', Auth::user()->id)->first();
                //     $file = $request->file('file');
                //     $extension = $file->getClientOriginalExtension();    
                //     $guid = GuidHelper::getGuid();
                //     $path = User::getUploadPath($user->id) . StringHelper::trimLower(Media::STORE);
                //     $name = "{$path}/{$guid}.{$extension}";  
                //     // $name = $file->getClientOriginalName();
                //     // array_push($imageName, $name);
                //     $media = new Media();
                //     $media->fill([
                //         'name' => $name,
                //         'extension' => $extension,
                //         'type' => Media::STORE,
                //         'user_id' => $user->id,
                //         'active' => true,
                //     ]);
            
                //     $media->save();
                //     // $image = Image::make($file)->save(public_path('image/product/') . $name);
                //     $image = Image::make($file);
                //         $image->orientate();
                //         $image->resize(1024, null, function ($constraint) {
                //             $constraint->aspectRatio();
                //             $constraint->upsize();
                //     });
                    
                //     $image->stream();
                //     Storage::put('/'. $name, $image->encode());
                // }
                $sellData = SellerData::where("user_id", Auth::user()->id)->first();
               $coverImage="";
                if($sellData->cover_image){
                    $hasPreviousImage = $sellData->getRawOriginal('cover_image');
                    $coverImage= $hasPreviousImage;
                }
                if($request->hasFile('file')){
                
                    // if (!empty($hasPreviousImage)) {
                    //     $previous_media = Auth::user()->media()->where('type', SellerData::MEDIA_UPLOAD)->first();
                    //     if($previous_media){
                    //         if(File::exists($previous_media->url)) {
                    //             File::delete($previous_media->url);
                    //         }
                    //         // Storage::delete('public/' . $hasPreviousImage);
                    //         $previous_media->delete();
                    //     }
                    // }
                    $uploadData = $this->uploadImage($request, $sellData);
                    $coverImage= $uploadData['url'];
                }
                $sellerData = SellerData::where('user_id', Auth::user()->id)
                ->update([
                    // 'user_id' => $request->user_id,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'city_id' => $request->city_id,
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'zip' => $request->zip,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'cover_image' => $coverImage,
                    'description' => $request->description
                ]);                     
                if($sellerData){
                    return response()->json(['status'=> true,'data' =>'You have SuccessFully Update Shop Data!'], 200);       
                }else{
                    return response()->json(['status'=> false,'data' =>"Unable to Update Shop Data"], 400);       
                } 
                // return $this->genericResponse(true, "You have SuccessFully Update Shop Data!", 200);
        }); 
       
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
    public function getSellOrder(Request $request){
        // return UserOrder::where('seller_id', Auth::user()->id)
        //     ->sum('order_total');
        //     ->count(*);//;
        // $payments = UserOrder::select('COUNT(id) AS count', 'SUM(order_total) AS sum')
        // ->where('seller_id', Auth::user()->id)
        // ->get();
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
         $userOrder = DB::table('tbl_user_order')
            // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, CONCAT(users.name,"-",users.last_name) as "SellerName"' )
            // ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum, seller_datas.fullname as "SellerName"' )
            ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum' )
            ->join('tbl_user_order_details', 'tbl_user_order_details.order_id', 'tbl_user_order.id')
            // ->join('users','tbl_user_order.seller_id','=','users.id')
            // ->join('seller_datas','tbl_user_order.seller_id','=','seller_datas.user_id')
            ->where('store_id', $seller->id)
            ->where('tbl_user_order.status', 'COMPLETED')
            // ->groupBy('id')
            ->first();
            if($userOrder->totalOrder != 0 || $userOrder->totalSum != null){
                // return $userOrder;
                return [
                    'totalOrder' => $userOrder->totalOrder,
                    'totalSum' => $userOrder->totalSum,
                    'SellerName' => $seller->fullname,
                ];
    
            }else{
                return [
                    'totalOrder' => 0,
                    'totalSum' => 0,
                    'SellerName' => $seller->fullname,
                ];
            } 

            // if($userOrder->totalOrder != 0 || $userOrder->totalSum != null){
            //     return response()->json(['status'=> true,'data' =>[$userOrder]], 200);       
            // }else{
            //     return response()->json(['status'=> false,'data' =>"Unable to Get Seller Data"], 400);       
            // } 
    }
    public function getSaveSeller(Request $request, $storeId){

        $saveseller = SaveSeller::where('shop_id', $storeId)
        // ->where('user_id', \Auth::user()->id)
        ->get();
        return $saveseller;
    }
    public function getUserSaveSeller(Request $request){

        $saveseller = SaveSeller::where('user_id', \Auth::user()->id)
        ->with('seller')
        ->get();
        return $saveseller;
    }
    public function setBankData(Request $request)
    {
        return DB::transaction(function () use ($request) {
            UserBank::where('user_id',\Auth::user()->id)->delete();
            $userbank = new UserBank();
            $userbank->user_id = \Auth::user()->id;
            $userbank->bank_id = $request->bank_id;
            $userbank->accountName = $request->accountName;
            $userbank->accountNumber = $request->accountNumber;
            $userbank->bic_swift = $request->bic_swift;
            $userbank->guid = GuidHelper::getGuid();
            $userbank->save();
            User::where('id',\Auth::user()->id)->update(['isTrustedSeller'=>true]);
            $user = User::where('id',\Auth::user()->id)->first();
            $user->notify(new SellerDataNotify($user));
            return [
                "user" => $user,
                "message" =>"Your Info is Save!"
            ];
        });
    }

    public function saveSeller(Request $request){
        return DB::transaction(function () use ($request) {
            $saveseller = new SaveSeller();
            $saveseller->shop_id = $request->get('shop_id');
            $saveseller->user_id = \Auth::user()->id;
            $saveseller->save();
            return "You have successfully saved the Seller Details!";
        });
    }

    // public function getSaveSeller(Request $request, $storeId){
    //     $saveseller = SaveSeller::where('shop_id', $storeId)
    //     ->where('user_id', 27,)//\Auth::user()->id)
    //     ->get();
    //     return $saveseller;
    // }
    public function updateBank(Request $request){
        return DB::transaction(function () use ($request) {
            $userbank = UserBank::where('user_id', \Auth::user()->id)
                ->update([
                    "bank_id" => $request->bank_id,
                    "accountName" => $request->accountName,
                    "accountNumber" => $request->accountNumber,
                    "bic_swift" => $request->bic_swift,
                ]);
            // $userbank = new UserBank();
            // $userbank->bank_id = $request->bank_id;
            // $userbank->accountName = $request->accountName;
            // $userbank->accountNumber = $request->accountNumber;
            // $userbank->bic_swift = $request->bic_swift;
            // $userbank->update();
          
            if($userbank){
                return response()->json(['status'=> true,'data' =>"You have SuccessFully Update Bank Details!"], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"You have Not Update Bank Details!"], 400);       
            }
        });
    }
    public function getBankDetails(Request $request){
            //return UserBank::where('user_id', \Auth::user()->id)->first();
            $userbank = UserBank::where('user_id', \Auth::user()->id)->first();
            if($userbank){
                return response()->json(['status'=> true,'data' =>$userbank], 200);       
            }else{
                return response()->json(['status'=> false,'data' =>"Unable To Get Bank"], 400);       
            }
    }

    public function getFeatured()
    {
        $seller = SellerData::where('active', true)
        ->where('featured', true)
        ->get();

        if($seller){
            return response()->json(['status'=> true,'data' =>$seller], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Featured Stores"], 400);       
        }
        
    }
    public function feedback(Request $request, $storeId){
        $feedback =Feedback::where('store_id', $storeId)->count();
        if($feedback){
            return response()->json(['status'=> true,'data' =>$feedback], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Featured Stores"], 400);       
        }

    }
    
}
