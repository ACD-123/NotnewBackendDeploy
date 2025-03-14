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
use App\Models\FeedBack;
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
use Validator;
use App\Models\SellerTransaction;
use App\Models\ReportSeller;
//use Carbon\Carbon;

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
    
    public function report(Request $request)
    {
        ReportSeller::create([
           "user_id"=> Auth::user()->id,
           "seller_guid"=>$request->seller_guid,
           "reason"=>$request->reason,
           "message"=>$request->message
        ]);
        return response()->json(['status'=> true,'data' => [],'message'=>"Request Submitted!"], 200);   
    }
    
    public function sellertransaction($guid,Request $request)
    {
        $total=SellerTransaction::where('seller_guid',$guid)->orderBy('created_at','DESC')->count();
        $page = $request->page ?? 1;
        $page_size = $request->page_size ?? 16;
        if($page=="undefined")
    {
        $page=1;
    }
    if($page_size=="undefined")
    {
        $page_size=16;
    }
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
        $transactions=SellerTransaction::where('seller_guid',$guid)->orderBy('created_at','DESC')->skip($skip)->take($page_size)->get();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

$earned = SellerTransaction::where('seller_guid', $guid)
                ->where('type', 'Order')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
                $refund = SellerTransaction::where('seller_guid', $guid)
                ->where('type', 'Refund')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
                $final=$earned-$refund;
        $ordersTotal=SellerTransaction::where('seller_guid',$guid)->where('type','Order')->sum("amount");
        $withdrawTotal=SellerTransaction::where('seller_guid',$guid)->where('type','Withdraw')->where('status','Pending')->sum("amount");
        $withdrawRejectedTotal=SellerTransaction::where('seller_guid',$guid)->where('type','Withdraw')->where('status','=','Rejected')->sum("amount");
        $refundTotal=SellerTransaction::where('seller_guid',$guid)->where('type','Refund')->sum("amount");
      
        $availableBalance=($ordersTotal)-($withdrawTotal+$refundTotal);
         return response()->json(['status'=> true,'data' => ["transactions"=>$transactions,"availableBalance"=>$availableBalance,"earned"=>$final,"pagination"=>$pagination]], 200);   
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

        $validator = Validator::make($request->all(), [
            'video' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:10240'
        ]);
       if($validator->fails()){
           return response()->json(['status'=> false,"message"=>"Video Format Or Size Issue! Video must be less than 10MB",'data' =>[]], 400); 
       }
        return DB::transaction(function () use ($request) {
            $checkUser = SellerData::where('user_id', Auth::user()->id)->first();
            if($checkUser)
            {
                $checkUser->country_id = $request->country;
                        $checkUser->state_id = $request->state;
                        $checkUser->city_id = $request->city;
                        $checkUser->fullname = $request->fullname;
                        $checkUser->email = $request->email;
                        $checkUser->phone = $request->phone;
                        $checkUser->address = $request->address;
                        $checkUser->zip = $request->zip;
                        $checkUser->description = $request->description;
                        $checkUser->save();
                        if($request->hasFile('file')){
                
                            $uploadData = $this->uploadImage($request, $checkUser);

                            SellerData::where('id',$checkUser->id)
                                ->update(['cover_image' => $uploadData['url']]);
                        }
                        if($request->hasFile('main_image')){
                
                            $uploadData = $this->uploadImageCover($request, $checkUser);

                            SellerData::where('id',$checkUser->id)
                                ->update(['main_image' => $uploadData['url']]);
                        }

                        if($request->hasFile('video')){
                
                            $uploadData = $this->uploadImage($request, $checkUser);

                            SellerData::where('id',$checkUser->id)
                                ->update(['video' => $uploadData['url']]);
                        }
                        return response()->json(['status'=> true,'data' =>"Seller Created SuccessFully"], 200);                       
            }else{
                $checkseller = SellerData::where('email', $request->email)->first();

            if($checkseller){
                return response()->json(['status'=> false,'data' =>"Email is already Exist"], 409);       
            }else{
                SellerData::where('user_id', \Auth::user()->id)->delete();
               
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
                        
                        $sellerData->guid = GuidHelper::getGuid();
                        $sellerData->save();
                        if($request->hasFile('file')){
                
                            $uploadData = $this->uploadImage($request, $sellerData);

                            SellerData::where('id',$sellerData->id)
                                ->update(['cover_image' => $uploadData['url']]);
                        }

                        if($request->hasFile('main_image')){
                
                            $uploadData = $this->uploadImageCover($request, $sellerData);

                            SellerData::where('id',$sellerData->id)
                                ->update(['main_image' => $uploadData['url']]);
                        }

                        if($request->hasFile('video')){
                
                            $uploadData = $this->uploadImage($request, $sellerData);

                            SellerData::where('id',$sellerData->id)
                                ->update(['video' => $uploadData['url']]);
                        }
                        
                        $selldata =  SellerData::where('id',$sellerData->id)->first();
                        $user = User::where('id', \Auth::user()->id)->first();
                        
                        if($selldata){
                            return response()->json(['status'=> true,'data' =>"Seller Created SuccessFully"], 200);       
                            // return response()->json(['status'=> true,'data' =>$selldata], 200);       
                        }else{
                            return response()->json(['status'=> false,'data' =>"Unable To Get Seller Data"], 400);       
                        }
    
            }   
            }
        }); 
        
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
        $seller->joined=date("Y",strtotime($seller->created_at));
        if($seller){
            return response()->json(['status'=> true,'data' =>$seller], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get Seller"], 400);       
        }
    }
    public function getShopInitialDetail($id)
    {
        $seller = SellerData::with('feedback')->where('guid',$id)->first();
          $seller->feedback_count=$seller->feedback->count()>0?$seller->feedback->count():1;
        $totalPositives=FeedBack::where('store_id', $seller->id)->where('is_positive',1)->count();
        $seller->positive=number_format(($totalPositives/$seller->feedback_count)*100,2);
        $seller->total_item_sold = UserOrderDetails::where("store_id", $seller->id)->count();
        $seller->ratingsPositive=FeedBack::where('store_id', $seller->id)->where('ratings','>',3)->count();
        $seller->ratingsNegative=FeedBack::where('store_id', $seller->id)->where('ratings', '>', 0)->where('ratings','<',2)->count();
        $seller->ratingsAverage=FeedBack::where('store_id', $seller->id)->where('ratings', '>=', 2)->where('ratings','<=',3)->count();
        if($seller){
            return response()->json(['status'=> true,'data' => $seller], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
        }
    }

    public function getProductsByShop($id,Request $request)
    {
        $seller = SellerData::where('guid', $id)->first();
        if($seller){
            $total=Product::where('shop_id', $seller->id)->where('name','LIKE','%'.$request->search_key.'%')->where('active',1)->where('is_deleted',0)->where('stockcapacity','>',0)->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages =ceil($total / $page_size);
            $pagination = [
                'total'=>$total,
                'page'=>$page,
                'page_size'=>$page_size,
                'total_pages'=>$total_pages,
                'remaining' =>$total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
           $items = Product::where('shop_id', $seller->id)->where('name','LIKE','%'.$request->search_key.'%')->where('active',1)->where('stockcapacity','>',0)->where('is_deleted',0)->skip($skip)->take($page_size)->get();
            $data=[
                "products"=>$items,
                "pagination"=>$pagination
            ];

            return response()->json(['status'=> true,'data' => $data], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
        }
    }
    public function getFeedBackByShop($id,Request $request)
    {
        $seller = SellerData::where('guid', $id)->first();
        if($seller){
            $total=FeedBack::where('store_id', $seller->id)->count();
            $page = $request->page ?? 1;
            $page_size = $request->page_size ?? 10;
            $skip = $page_size * ($page - 1);
            $total_pages =ceil($total / $page_size);
            $pagination = [
                'total'=>$total,
                'page'=>$page,
                'page_size'=>$page_size,
                'total_pages'=>$total_pages,
                'remaining' =>$total_pages - $page,
                'next_page' => $total_pages > $page ? $page + 1 : $total_pages,
                'prev_page' => $page > 1 ? $page - 1 : 1,
            ];
            $items = FeedBack::with('product','user')->where('store_id', $seller->id)->skip($skip)->take($page_size)->get();
            $totalPositives=FeedBack::where('store_id', $seller->id)->where('is_positive',1)->count();
            $totalFeedbacks=$total>0?$total:1;
            $data=[
                "feedback"=>$items,
                "pagination"=>$pagination,
                "positive"=>number_format(($totalPositives/$totalFeedbacks)*100,2),
                "description_accuracy"=>"4.8",
                "shipping_cost"=>"4.5",
                "delivery_speed"=>"5",
                "customer_care"=>"4.2"
            ];

            return response()->json(['status'=> true,'data' => $data], 200);       
        }else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
        }
    }
    public function getAboutDataByShop($id)
    {
        $seller = SellerData::where('guid', $id)->first();
        if($seller){
        $about =[
            "video" => $seller->video,
            "description" => $seller->description,
            "name" => $seller->fullname,
            "joined" => date("Y-m-d",strtotime($seller->created_at)),
            "location" => $seller->city_id .' - '.$seller->country_id,
            "email"=>$seller->email,
            "phone"=>$seller->phone
            ];
            return response()->json(['status'=> true,'data' =>$about], 200);     
        }
        else{
            return response()->json(['status'=> false,'data' =>"Unable To Get  Shop"], 400);       
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
            $about =[
                "video" => $seller->video,
                "aboutus" => $seller->description,
                "name" => $seller->fullname,
                "joined" => $date,
                "location" => $seller->city_id .' - '.$seller->country_id
                ];
            
            $data=[
                "main_image" => env("APP_URL").$seller->main_image,
                "cover_image" => env('APP_URL').$seller->cover_image,
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
        
    
        return DB::transaction(function () use ($request) {
                $name="";
                
                $sellData = SellerData::where("user_id", Auth::user()->id)->first();
               $coverImage="";
               $mainImage="";
               $video="";
                if($sellData->cover_image){
                    $hasPreviousImage = $sellData->getRawOriginal('cover_image');
                    $coverImage= $hasPreviousImage;
                }
                if($sellData->main_image){
                    $hasPreviousImage = $sellData->getRawOriginal('main_image');
                    $mainImage= $hasPreviousImage;
                }
                if($sellData->video){
                    $video=$sellData->video;
                }
                if($request->hasFile('file')){
                
                    
                    $uploadData = $this->uploadImage($request, $sellData);
                    $coverImage= $uploadData['url'];
                }
                if($request->hasFile('main_image')){
                
                    
                    $uploadData = $this->uploadImageCover($request, $sellData);
                    $mainImage= $uploadData['url'];
                }
                if($request->hasFile('video')){
                    $uploadData = $this->uploadImage($request, $sellData);
                    $video=$uploadData['url'];
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
                    'main_image'=>$mainImage,
                    'description' => $request->description,
                    'video'=>$video
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
        
        $seller = SellerData::where('user_id', Auth::user()->id)
        ->first();
        $user=User::find($seller->user_id);
         $userOrder = DB::table('tbl_user_order')
            ->selectRaw('count(*) as totalOrder, sum(order_total) as totalSum' )
            ->join('tbl_user_order_details', 'tbl_user_order_details.order_id', 'tbl_user_order.id')
            
            ->where('store_id', $seller->id)
            ->where('tbl_user_order_details.status', 'COMPLETED')
            
            ->first();
            $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

$earned = SellerTransaction::where('seller_guid', $seller->guid)
                ->where('type', 'Order')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
                $withdraw = SellerTransaction::where('seller_guid', $seller->guid)
                ->where('type', 'Withdraw')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount');
            if($userOrder->totalOrder != 0 || $userOrder->totalSum != null){
                return [
                    'totalOrder' => $userOrder->totalOrder,
                    'totalSum' => $earned - $withdraw,
                    'SellerName' => $seller->fullname,
                     'sellerGuid'=>$seller->guid,
                     'isTrustedSeller'=>$user->isTrustedSeller
                ];
    
            }else{
                return [
                    'totalOrder' => 0,
                    'totalSum' => 0,
                    'SellerName' => $seller->fullname,
                     'sellerGuid'=>$seller->guid,
                     'isTrustedSeller'=>$user->isTrustedSeller
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
            $userbank = UserBank::with('bank')->where('user_id', \Auth::user()->id)->first();
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
    public function sellerWithdrawList(Request $request)
    {
        try {
            $total = SellerTransaction::where('type','Withdraw')->count();
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
            $transactions=SellerTransaction::with('seller','seller.user')->where('type','Withdraw')->orderBy('created_at','DESC')->skip($skip)->take($page_size)->get();
            return customApiResponse(true, ["transactions" => $transactions, "pagination" => $pagination], 'Users!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong!', 500);
        }
    }
    public function sellerWithdraw($guid,Request $request)
    {
        try {
            SellerTransaction::create([
                'seller_guid' => $guid,
                'amount' => $request->amount,
                'status' => 0,
                'type' => "Withdraw",
                'order_id' => 0,
                'message' => "Your Withdraw of $" . $request->amount . " is under review from Admin!",
                "total" => $request->amount,
                "status"=>"Pending"
            ]);
            return customApiResponse(true, [], 'Withdraw Request Send to Admin!');            
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong!', 500);
        }
    }
    
}
