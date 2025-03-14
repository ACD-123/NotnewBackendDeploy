<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRooms;
use App\Models\ChatMessages;
use Validator;
use DB;
use App\Models\User;
use App\Models\UserOrder;
use App\Models\UserOrderDetails;
use App\Models\SellerData;
use App\Events\ChatEvent;
use App\Helpers\StripeHelper;

class ChatRoomsController extends Controller
{
    public function createChatRooms(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'participants' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'data'=> []
            ];
            return response()->json($response, 404);
        }

        $matchThese = ['uid' => $request->uid, 'participants' => $request->participants,'status'=>$request->status];
        $matchTheseToo = ['uid' => $request->participants, 'participants' => $request->uid,'status'=>$request->status];
        $test = ChatRooms::where($matchThese)->count();
        $test2 = ChatRooms::where($matchTheseToo)->count();
        $chats=$test+$test2;
        if($chats>0){
            $data = ChatRooms::where($matchThese)->first();
            if(is_null($data))
            {
                $data = ChatRooms::where($matchTheseToo)->first();
            }
            $response = [
                'data'=>$data,
                'status' => true,
                'message' => "",
            ];
            return response()->json($response, 200);
        }
        $data = ChatRooms::create($request->all());
        if (is_null($data)) {
            $response = [
            'data'=>$data,
            'message' => 'error',
            'status' => false,
        ];
        return response()->json($response, 400);
        }
        $messageNew=ChatMessages::create([
            "room_id"=>$data->id,
            "uid"=>$request->participants,
            "from_id"=>$request->uid,
            "message_type"=>0,
            "message"=>"Hello!",
            "status"=>1
        ]);
        event(new ChatEvent($request->participants, $messageNew));
        $user=User::find($request->uid);
        if($user)
        {
            $seller=SellerData::where('guid',$request->participants)->first();

            $arr=array(
                "title"=>"You recieved a new Message",
                "message"=>"You have recieved a new Message from ".$user->name,
                "user_id"=>$seller->user_id,
                "type"=>"chats",
                "sender_id"=>$request->participants,
                "notification_type"=>"chat",
                "recieved_from"=>0,
                "room_id"=>$data->id
                );
                StripeHelper::saveNotification($arr);
        }
        else{
            $seller=SellerData::where('guid',$request->participants)->first();
            $user=User::find($seller->user_id);
            $arr=array(
                "title"=>"You recieved a new Message",
                "message"=>"You have recieved a new Message from ".$seller->fullname,
                "user_id"=>$request->uid,
                "type"=>"chats",
                "sender_id"=>$user->id,
                "notification_type"=>"chat",
                "recieved_from"=>1,
                "room_id"=>$data->id
                );
                StripeHelper::saveNotification($arr);
        }
        $response = [
            'data'=>$data,
            'status' => true,
            'message' => "",
        ];
        return response()->json($response, 200);
    }

    public function getChatRooms(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'participants' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'data'=> []
            ];
            return response()->json($response, 404);
        }

        \DB::enableQueryLog();

        $matchThese = ['uid' => $request->uid, 'participants' => $request->participants];
        $matchTheseToo = ['uid' => $request->participants, 'participants' => $request->uid];
        $data = ChatRooms::where($matchThese)->first();
        $data2 = ChatRooms::where($matchTheseToo)->first();
        if (is_null($data) && is_null($data2)) {
            $response = [
                'status' => false,
                'message' => 'Data not found.',
                'data' => []
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'data2'=>$data2,
            'status' => true,
            'message' => ""
        ];
        return response()->json($response, 200);
    }


    public function getChatListBUid(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'data'=> []
            ];
            return response()->json($response, 404);
        }
        $status=$request->status??1;
        if($status==1)
        {
            $chats=ChatRooms::where('status',$status)->where('uid',$request->id)->orWhere('participants',$request->id)->get();
            $data=[];
          
            foreach($chats as $chat){
             
                $sender_name="";
                $sender_last_name="";
                $profile_image="";
                if($request->id==$chat->uid){
                    $seller=SellerData::where('guid',$chat->participants)->first();
                    $sender_name=$seller->fullname;
                    $sender_last_name=$seller->fullname;
                    $profile_image=$seller->cover_image;
                }
                if($request->id==$chat->participants){
                    $seller=SellerData::where('guid',$chat->uid)->first();
                    $sender_name=$seller->fullname;
                    $sender_last_name=$seller->fullname;
                    $profile_image=$seller->cover_image;
                }
                $message = ChatMessages::select('message', 'created_at')
                ->where('room_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->first();
                $readCount=ChatMessages::where('room_id',$chat->id)->where('uid',$request->id)->where('is_read',0)->count();
                $seenCount=ChatMessages::where('room_id',$chat->id)->where('uid',$request->id)->where('is_seen',0)->count();

                if ($message) {
                    $chat->date = date("h:i A", strtotime($message->created_at));
                    $chat->date_new = date("Y-m-d H:i:s", strtotime($message->created_at));
                    $chat->message = $message->message;
                } else {
                    $chat->date = "";
                    $chat->date_new = "";
                    $chat->message = "";
                }
                $arr=[
                    "uid"=>$chat->uid,
                    "participants"=>$chat->participants,
                    "sender_name"=>$sender_name,
                    "sender_last_name"=>$sender_last_name,
                    "receiver_name"=>$sender_name,
                    "receiver_last_name"=>$sender_name,
                    "sender_profile_image"=>$profile_image,
                    "receiver_profile_image"=>$profile_image,
                    "id"=>$chat->id,
                    "date"=>$chat->date,
                    "date_new"=>$chat->date_new,
                    "message"=>$chat->message,
                    "read_count"=>$readCount,
                        "seen_count"=>$seenCount,
                ];
                $data[]=$arr;
            }
            usort($data, function($a, $b) {
                return strtotime($b['date_new']) - strtotime($a['date_new']);
            });
            $response = [
                'data'=>$data,
                'status' => true,
                'message' => "",
            ];
            return response()->json($response, 200);
        }

        if($status==0)
        {
            $chats=ChatRooms::where('status',$status)->where('uid',$request->id)->orWhere('participants',$request->id)->get();
            $data=[];
            foreach($chats as $chat){
               
                $sender_name="";
                $sender_last_name="";
                $profile_image="";
                $senderId=0;
                if($request->id==$chat->uid){
                    $user=User::find($chat->participants);
                    $sender_name=$user->name;
                    $sender_last_name=$user->last_name;
                    $profile_image=$user->profile_image;
                }
                if($request->id==$chat->participants){
                    $user=User::with('media')->find($chat->uid);
                    $sender_name=$user->name;
                    $sender_last_name=$user->last_name;
                    $profile_image=$user->profile_image;
                }
                $message = ChatMessages::select('message', 'created_at')
                ->where('room_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->first();
                $readCount=ChatMessages::where('room_id',$chat->id)->where('uid',$request->id)->where('is_read',0)->count();
                $seenCount=ChatMessages::where('room_id',$chat->id)->where('uid',$request->id)->where('is_seen',0)->count();
                if ($message) {
                    $chat->date = date("h:i A", strtotime($message->created_at));
                    $chat->date_new = date("Y-m-d H:i:s", strtotime($message->created_at));
                    $chat->message = $message->message;
                } else {
                    $chat->date = "";
                    $chat->date_new = "";
                    $chat->message = "";
                }
                    $arr=[
                        "uid"=>$chat->uid,
                        "participants"=>$chat->participants,
                        "sender_name"=>$sender_name,
                        "sender_last_name"=>$sender_last_name,
                        "receiver_name"=>$sender_name,
                        "receiver_last_name"=>$sender_name,
                        "sender_profile_image"=>$profile_image,
                        "receiver_profile_image"=>$profile_image,
                        "id"=>$chat->id,
                        "date"=>$chat->date,
                        "date_new"=>$chat->date_new,
                        "message"=>$chat->message,
                        "read_count"=>$readCount,
                        "seen_count"=>$seenCount,
                    ];
                 $data[]=$arr;
                }
                usort($data, function($a, $b) {
                    return strtotime($b['date_new']) - strtotime($a['date_new']);
                });
            $response = [
                'data'=>$data,
                'status' => true,
                'message' => "",
            ];
        return response()->json($response, 200);
        }
    }
     public function getChatUsers($userID,$status)
     {
         $user=ChatRooms::where('uid',$userID)
        ->orWhere('participants',$userID)->get();
        $uids = $user->pluck('uid')->toArray();
        $participants = $user->pluck('participants')->toArray();
        $mergedIds = array_unique(array_merge($uids, $participants));
       
        $mergedIds[] = 247;
        $usersNotInChatRooms = [];
        if($status==0)
        {
            $seller=SellerData::where('guid',$userID)->first();
            $orderIds = UserOrderDetails::where('store_id', $seller->id)->groupBy('order_id')->pluck('order_id')->toArray();
            $userIds=UserOrder::whereIn('id', $orderIds)->groupBy('buyer_id')->pluck('buyer_id')->toArray();
            $filteredUserIds = array_diff($userIds, $mergedIds);
            $usersNotInChatRooms = User::with('media')->whereIn('id', $filteredUserIds)->get();
        }
        if($status==1)
        {
            $orderIds=UserOrder::where('buyer_id',$userID)->groupBy('id')->pluck('id')->toArray();
            $sellerIds = UserOrderDetails::whereIn('order_id', $orderIds)->groupBy('store_id')->pluck('store_id')->toArray();
            $sellerGuIds = SellerData::whereIn('id', $sellerIds)->groupBy('guid')->pluck('guid')->toArray();  
            $filteredUserIds = array_diff($sellerGuIds, $mergedIds);
            $usersNotInChatRooms= SellerData::whereIn('guid', $filteredUserIds)->get();
        }
        $response = [
            'data'=>$usersNotInChatRooms,
            'status' => true,
            'message' => "",
        ];
        return response()->json($response, 200);
     }
}
