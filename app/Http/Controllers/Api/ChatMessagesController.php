<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessages;
use App\Models\ChatRooms;
use App\Models\User;
use Validator;
use DB;
use App\Helpers\StripeHelper;
use Auth;
use App\Models\SellerData;

class ChatMessagesController extends Controller
{
    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'room_id'=>'required',
            'uid'=>'required',
            'from_id' => 'required',
            'message_type' => 'required',
            'message' => 'required',
            'status'=>'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = ChatMessages::create($request->all());
        if (is_null($data)) {
            $response = [
            'data'=>$data,
            'message' => 'error',
            'status' => false,
        ];

        return response()->json($response, 400);
        }
        event(new ChatEvent($request->uid, $data));
        
                $user=User::find($request->from_id);
                if($user)
                {
                    $seller=SellerData::where('guid',$request->uid)->first();

                    $arr=array(
                        "title"=>"You recieved a new Message",
                        "message"=>"You have recieved a new Message from ".$user->name,
                        "user_id"=>$seller->user_id,
                        "type"=>"chats",
                        "sender_id"=>$request->from_id,
                        "notification_type"=>"chat",
                        "recieved_from"=>0,
                        "room_id"=>$request->room_id
                        );
                        StripeHelper::saveNotification($arr);
                }
                else{
                    $seller=SellerData::where('guid',$request->from_id)->first();
                    $user=User::find($seller->user_id);
                    $arr=array(
                        "title"=>"You recieved a new Message",
                        "message"=>"You have recieved a new Message from ".$seller->fullname,
                        "user_id"=>$request->uid,
                        "type"=>"chats",
                        "sender_id"=>$user->id,
                        "notification_type"=>"chat",
                        "recieved_from"=>1,
                        "room_id"=>$request->room_id
                        );
                        StripeHelper::saveNotification($arr);
                }
        
        $response = [
            'data'=>$data,
            'message' => "",
            'status' => true,
        ];
        return response()->json($response, 200);
    }

    public function getById(Request $request){
        $user=Auth::user();
        $seller=SellerData::where('user_id',$user->id)->first();
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'data'=> []
            ];
            return response()->json($response, 404);
        }

        $data = ChatMessages::where('room_id',$request->room_id)->get();
        
        ChatMessages::where('room_id',$request->room_id)->where('uid',$request->id)->update([
            "is_seen"=>1,
            "is_read"=>1,
        ]);
        
        foreach($data as $chat){
            
            if($user)
            {
                if($chat->from_id==$user->id)
                {
                     $chat->user=User::with('media')->find($chat->from_id); 
            $chat->participants=User::with('media')->find($chat->uid);
            $seller=SellerData::where('guid',$chat->from_id)->first();
            if($seller)
            {
                $chat->seller=$seller;
                $chat->testuser=User::with('media')->find($chat->uid);
            }
            else{
                $seller=SellerData::where('guid',$chat->uid)->first();
                $chat->seller=$seller;
                $chat->testuser=User::with('media')->find($chat->from_id);
            }
                }
                else{
                    $chat->user=User::with('media')->find($chat->from_id); 
                    $chat->participants=User::with('media')->find($chat->uid);
                    $seller=SellerData::where('guid',$chat->from_id)->first();
                    if($seller)
                    {
                        $chat->seller=$seller;
                        $chat->testuser=User::with('media')->find($chat->uid);
                    }
                    else{
                        $seller=SellerData::where('guid',$chat->uid)->first();
                        $chat->seller=$seller;
                        $chat->testuser=User::with('media')->find($chat->from_id);
                    }
                }
              
            }
            else{
                $chat->user=User::with('media')->find($chat->from_id); 
                $chat->participants=User::with('media')->find($chat->uid);
                $seller=SellerData::where('guid',$chat->from_id)->first();
                if($seller)
                {
                    $chat->seller=$seller;
                    $chat->testuser=User::with('media')->find($chat->uid);
                }
                else{
                    $seller=SellerData::where('guid',$chat->uid)->first();
                    $chat->seller=$seller;
                    $chat->testuser=User::with('media')->find($chat->from_id);
                }
            }
        }

        if (is_null($data)) {
            $response = [
                'status' => false,
                'message' => 'Data not found.',
                'data' => []
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'status' => true,
            'message' => "data",
        ];
        return response()->json($response, 200);
    }

    public function deleteById(Request $request){
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'status' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'data'=> []
            ];
            return response()->json($response, 404);
        }

        $data = ChatMessages::where('room_id',$request->room_id)->get();

        if (is_null($data)) {
            $response = [
                'status' => false,
                'message' => 'Data not found.',
                'data' => []
            ];
            return response()->json($response, 404);
        }
        foreach($data as $message)
        {
            $message->delete();
        }
        ChatRooms::find($request->room_id)->delete();

        $response = [
            'data'=>[],
            'status' => true,
            'message' => "deleted",
        ];
        return response()->json($response, 200);
    }
    public function updateIsSeen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id'=>'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        ChatMessages::where('room_id',$request->room_id)->update([
            "is_seen"=>1
        ]);
        $response = [
            'data'=>[],
            'status' => true,
            'message' => "Updated",
        ];
        return response()->json($response, 200);
    }

    public function updateIsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id'=>'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        ChatMessages::where('room_id',$request->room_id)->update([
            "is_read"=>1
        ]);
        $response = [
            'data'=>[],
            'status' => true,
            'message' => "Updated",
        ];
        return response()->json($response, 200);
    }
}
