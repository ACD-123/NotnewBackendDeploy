<?php

namespace App\Helpers;

use App\Events\NotificationEvent;
use App\Models\User;
use Stripe\StripeClient;
use App\Models\UserNotificationModel;
use App\Services\FCMService;

class StripeHelper
{
   
    public static function createAccountLink(User $user)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        return $stripe->accountLinks->create([
            'account' => $user->stripe_account_id,
            'refresh_url' => env('STRIPE_REFRESH_URL'),
            'return_url' => env('STRIPE_RETURN_URL'),
            'type' => 'account_onboarding'
        ]);
    }
    public static function checkAccount(User $user)
    {
        $stripe = new StripeClient(env('STRIPE_SK'));
        return $stripe->accounts->retrieve(
            $user->stripe_account_id,
            []
        );
    }
    public static function saveNotification($data)
    {
        $sendUser=true;
        $user=User::find($data['user_id']);
        if($data['type']=="chats" && $user->chats_notification==0){
            $sendUser=false;
        }
        if($data['type']=="buying" && $user->buying_notification==0){
            $sendUser=false;
        }
        if($data['type']=="selling" && $user->selling_notification==0){
            $sendUser=false;
        }
        if($data['type']=="auction" && $user->auction_notification==0){
            $sendUser=false;
        }
        if($data['type']=="important" && $user->important_notification==0){
            $sendUser=false;
        }
        if($sendUser==true){
            $notification=UserNotificationModel::create([
                "user_id"=>$data['user_id'],
                "title"=>$data['title'],
                "message"=>$data['message'],
                "type"=>$data['type'],
                "sender_id"=>$data['sender_id'],
                "is_read"=>0,
                "is_seen"=>0,
                "notification_type"=>$data['notification_type']??"",
                "recieved_from"=>$data['recieved_from']??"",
                "product_guid"=>$data['product_guid']??"",
                "room_id"=>$data['room_id']??"",
                "win"=>$data['win']??0,
                "url"=>$data['url']??"",
                "guid"=>$data['guid']??"",
                "notificationtype"=>$data['notificationtype']??"",
                "image"=>$data['image']??"",
                "auction_status"=>$data['auctioned']??0
            ]);
    
            $sendNotification=UserNotificationModel::with(['user','sender'])->where('id',$notification->id)->first();
            event(new NotificationEvent($data['user_id'], $sendNotification));
            
            $fcmService=new FCMService();
            if(!empty($user->fcm_web_token) && $user->fcm_web_token!=null && $user->fcm_web_token!=""){
                $url="";
                if($data['notification_type']=="chat"){
                    if($data['recieved_from']==1){
                        $url=env('WEB_FRONTEND_URL')."customerdashboard?tab=messages&room-id=".$data['room_id'];
                    }
                    if($data['recieved_from']==0){
                        $url=env('WEB_FRONTEND_URL')."my-seller-account?tab=chat&room-id=".$data['room_id'];
                    }
                }
                if($data['type']=="buying"){
                    $url=env('WEB_FRONTEND_URL')."customerdashboard?tab=activity&component=my-orders";
                }
                if($data['type']=="selling"){
                    $url=env('WEB_FRONTEND_URL')."my-seller-account?tab=order-management";
                }
                if($data['type']=="auction"){
                    if($sendNotification->win==0){
                        $url=env('WEB_FRONTEND_URL')."auctionproduct/".$data['product_guid'];
                    }
                    else{
                        $url=env('WEB_FRONTEND_URL')."cart";
                    }    
                }
                if($data['type']=="important"){
                    $url=$data['url'];
                }
                $fcmService->sendNotification($user->fcm_web_token,$data['title'],$data['message'],$url);
            }
            if(!empty($user->fcm_token) && $user->fcm_token!=null && $user->fcm_token!=""){
                $fcmService->sendNotification($user->fcm_token,$data['title'],$data['message']);
            }
        }
    }
}
