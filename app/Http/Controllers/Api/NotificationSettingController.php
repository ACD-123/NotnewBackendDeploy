<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return NotificationSetting::with(['user'])->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
            $data =[
                'order_updates' => $request->get('order_updates'),
                'item_ending' => $request->get('item_ending'),
                'item_updates' => $request->get('item_updates'),
                'auction_updates' => $request->get('auction_updates'),
                'offer_updates'=> $request->get('offer_updates'),
                'presonalized_recomendations' =>$request->get('presonalized_recomendations'),
                'rewards_offers' =>$request->get('rewards_offers'),
                'general_promotions' =>$request->get('general_promotions'),
                'selling_presonalized_recomendations' =>$request->get('selling_presonalized_recomendations'),
                'selling_rewards_offers' =>$request->get('selling_rewards_offers'),
                'selling_general_promotions' =>$request->get('selling_general_promotions'),
                'user_id' => \Auth::user()->id,
            ];
            $check = NotificationSetting::where('user_id', \Auth::user()->id)->first();
            if($check){
                $notificationsettings = NotificationSetting::where('user_id', \Auth::user()->id)->update($data);
                if($notificationsettings){
                    return response()->json(['status'=>'true','data'=>'Notification setting has been Changed!'],200);
                }else{
                    return response()->json(['status'=>'false','message'=>'Unable to Save Notification setting!'],500);
                }
            }
            else{
                $notificationsettings = new NotificationSetting();
                $notificationsettings->fill($data)->save();
                if($notificationsettings){
                    return response()->json(['status'=>'true','data'=>'Notification setting has been Changed!'],200);
                }else{
                    return response()->json(['status'=>'false','message'=>'Unable to Save Notification setting!'],500);
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
    public function show()
    {
        return  NotificationSetting::where('user_id', \Auth::user()->id)->first();
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
    public function update(Request $request, )
    {
        return DB::transaction(function () use ($request) {  
            $data =[
                'order_updates' => $request->get('order_updates'),
                'item_ending' => $request->get('item_ending'),
                'item_updates' => $request->get('item_updates'),
                'auction_updates' => $request->get('auction_updates'),
                'offer_updates'=> $request->get('offer_updates'),
                'presonalized_recomendations' =>$request->get('presonalized_recomendations'),
                'rewards_offers' =>$request->get('rewards_offers'),
                'general_promotions' =>$request->get('general_promotions'),
                'selling_presonalized_recomendations' =>$request->get('selling_presonalized_recomendations'),
                'selling_rewards_offers' =>$request->get('selling_rewards_offers'),
                'selling_general_promotions' =>$request->get('selling_general_promotions'),
            ];
            $notificationsettings = NotificationSetting::where('user_id', \Auth::user()->id)->update($data);
            if($notificationsettings){
                return response()->json(['status'=>'true','data'=>'Notification setting has been Changed!'],200);
            }else{
                return response()->json(['status'=>'false','message'=>'Unable to Save Notification setting!'],500);
            }
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
}
