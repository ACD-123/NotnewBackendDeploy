<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatEvent;
use App\Models\PostChatRoom;
use App\Models\AdminSetting;
use App\Models\PostProduct;
use App\Models\PostChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use DB; 
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\File;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PostChatController extends Controller
{
    public function createChatRoom(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $validator = Validator::make($request->all(), [
                'participant_id' => 'required',
                'guid'=>'required'
            ]);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $category=PostProduct::where('guid',$request->guid)->first();
            if(!$category){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            if($category->user_id==$user->id){
                return customApiResponse(false, [], 'You have Posted this Product!', 404);
            }
            DB::beginTransaction();
            $matchThese = ['user_id' => $user->id, 'participant_id' => $request->participant_id,'post_product_guid'=>$request->guid];
            $matchTheseToo = ['user_id' => $request->participant_id, 'participant_id' => $user->id,'post_product_guid'=>$request->guid];
            $test = PostChatRoom::where($matchThese)->count();
            $test2 = PostChatRoom::where($matchTheseToo)->count();
            $chats = $test + $test2;
            if ($chats > 0) {
                $data = PostChatRoom::where($matchThese)->first();
                if (is_null($data)) {
                    $data = PostChatRoom::where($matchTheseToo)->first();
                }
                $user = User::with('media')->where('id', $user->id)->first();
                $reciever = User::with('media')->where('id', $data->participant_id)->first();
                if ($user->id==$data->participant_id) {
                    $user = User::with('media')->where('id', $data->participant_id)->first();
                    $reciever = User::with('media')->where('id', $user->id)->first();
                }
                if ($data->deleted_by == $user->id) {
                    $data->update([
                        'deleted_by' => null
                    ]);
                }
                $data->user = $user;
                $data->reciever = $reciever;
                return customApiResponse(true, ["chat_room" => $data], 'Chat Room!');
            }
            $data = PostChatRoom::create([
                'user_id' => $user->id,
                'participant_id' => $request->participant_id,
                'post_product_guid' => $request->guid
            ]);
            if (is_null($data)) {
                return customApiResponse(false, [], 'Failed to create chat room!', 400);
            }
            $user = User::with('media')->where('id', $user->id)->first();
            $reciever = User::with('media')->where('id', $data->participant_id)->first();
            if ($user->id==$data->participant_id) {
                $user = User::with('media')->where('id', $data->participant_id)->first();
                $reciever = User::with('media')->where('id', $user->id)->first();
            }
            $data->user = $user;
            $data->reciever = $reciever;
            DB::commit();
            return customApiResponse(true, ["chat_room" => $data], 'Chat Room!');
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getChatList(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $chatRooms = PostChatRoom::where(function ($query) use ($user) {
                $query->where('deleted_by', '!=', $user->id)
                    ->orWhereNull('deleted_by'); 
            })
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('participant_id', $user->id);
                })
                ->with([
                    'messages' => function ($query) use ($user) {
                        $query->where(function ($query) use ($user) {
                            $query->where('deleted_by', '!=', $user->id)
                                ->orWhereNull('deleted_by'); // Include messages where deleted_by is NULL
                        })->latest('created_at')->limit(1);
                    }
                ])
                ->get()
                ->sortByDesc(function ($chatRoom) {
                    return optional($chatRoom->messages->first())->created_at; // Sort by the last message's created_at
                });
                $chatRooms = $chatRooms->filter(function ($chatRoom) {
                    return $chatRoom->messages->isNotEmpty();
                });
            $chatRooms->each(function ($chatRoom) use ($user) {
                $chatRoom->unread_count = PostChatMessage::where('room_id', $chatRoom->id)->where('user_id', $user->id)->where('deleted_by', $user->id)->where('is_read', 0)->count();
                $user = User::with('media')->where('id', $user->id)->first();
                $reciever = User::with('media')->where('id', $chatRoom->participant_id)->first();
                if ($user->id==$chatRoom->participant_id) {
                    $user = User::with('media')->where('id', $chatRoom->participant_id)->first();
                    $reciever = User::with('media')->where('id', $user->id)->first();
                }
                $chatRoom->user = $user;
                $chatRoom->reciever = $reciever;
                $chatRoom->product=PostProduct::with('category','extra','images','user','user.media')->where('guid',$chatRoom->post_product_guid)->first();
            });
            $chatRoomsArray = $chatRooms->values()->toArray();
            $chatRoomsArray = $chatRooms->filter(function ($chatRoom) use ($request) {
                $searchKey = $request->search_key ?? null;
                if ($searchKey) {
                    return str_contains(strtolower(optional($chatRoom->reciever)->name ?? ''), strtolower($searchKey));
                }
                return true;
            })->values()->toArray();
            return customApiResponse(true, ["chat_rooms" => $chatRoomsArray], 'Chat List!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getChatRoomByID(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $chatRoom = PostChatRoom::where('id', $id)->first();
            if (!$chatRoom) {
                return customApiResponse(false, [], 'Chat Room Not Found!', 400);
            }
            $user = User::with('media')->where('id', $user->id)->first();
            $reciever = User::with('media')->where('id', $chatRoom->participant_id)->first();
            if ($user->id==$chatRoom->participant_id) {
                $user = User::with('media')->where('id', $chatRoom->participant_id)->first();
                $reciever = User::with('media')->where('id', $user->id)->first();
            }
            $chatRoom->user = $user;
            $chatRoom->reciever = $reciever;
            $chatRoom->product=PostProduct::with('category','extra','images','user','user.media')->where('guid',$chatRoom->post_product_guid)->first();
            return customApiResponse(true, ["chat_room" => $chatRoom], 'Chat Room!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function deleteChatRoom(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $chatRoom = PostChatRoom::where('id', $id)->first();
            if (!$chatRoom) {
                return customApiResponse(false, [], 'Chat Room Not Found!', 400);
            }
            if ($chatRoom->deleted_by != NULL && $chatRoom->deleted_by != $user->id) {
                PostChatMessage::where('room_id', $chatRoom->id)->delete();
                $chatRoom->delete();
                return customApiResponse(true, [], 'Chat Room Deleted Successfully!');
            }
            if ($chatRoom->deleted_by != NULL && $chatRoom->deleted_by == $user->id) {
                $messages = PostChatMessage::where('room_id', $chatRoom->id)->whereNull('deleted_by')->get();
                foreach ($messages as $value) {
                    $value->update([
                        'deleted_by' => $user->id
                    ]);
                }
                return customApiResponse(true, [], 'Chat Room Deleted Successfully!');
            }
            if ($chatRoom->deleted_by == NULL) {
                $messages = PostChatMessage::where('room_id', $chatRoom->id)->whereNull('deleted_by')->get();
                foreach ($messages as $value) {
                    $value->update([
                        'deleted_by' => $user->id
                    ]);
                }
                $chatRoom->update([
                    'deleted_by' =>  $user->id
                ]);
                return customApiResponse(true, [], 'Chat Room Deleted Successfully!');
            }

        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getChatUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $count = PostChatMessage::where('is_read', 0)->where('is_seen', 0)->where('user_id', $user->id)->count();
            return customApiResponse(true, ['count' => $count], 'Chat Unread Count Retrieved Successfully');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function getChatRoomWithMessages(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $chatRoom = PostChatRoom::where('id', $id)->first();
            if (!$chatRoom) {
                return customApiResponse(false, [], 'Chat Room Not Found!', 400);
            }
            $user = User::with('media')->where('id', $user->id)->first();
            $reciever = User::with('media')->where('id', $chatRoom->participant_id)->first();
            if ($user->id==$chatRoom->participant_id) {
                $user = User::with('media')->where('id', $chatRoom->participant_id)->first();
                $reciever = User::with('media')->where('id', $user->id)->first();
            }
            $chatRoom->user = $user;
            $chatRoom->reciever = $reciever;
            $chatRoom->product=PostProduct::with('category','extra','images','user','user.media')->where('guid',$chatRoom->post_product_guid)->first();
            $total = PostChatMessage::where('room_id', $chatRoom->id)
                ->where(function ($query) use ($user) {
                    $query->whereNull('deleted_by')
                        ->orWhere('deleted_by', '!=', $user->id);
                })->count();
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
            $messages = PostChatMessage::where('room_id', $chatRoom->id)
                ->where(function ($query) use ($user) {
                    $query->whereNull('deleted_by')
                        ->orWhere('deleted_by', '!=', $user->id);
                })->orderBy('created_at', 'DESC')->skip($skip)->take($page_size)->get();
            foreach ($messages as $value) {
                $value->from = User::with('media')->where('id', $value->from_id)->first();
            }
            PostChatMessage::where('room_id', $chatRoom->id)->where('user_id', $user->id)->update([
                "is_seen" => 1,
                "is_read" => 1,
            ]);
            return customApiResponse(true, ["chat_room" => $chatRoom, "messages" => $messages, "pagination" => $pagination], 'Chat Room With Messages!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function createMessage(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return customApiResponse(false, [], 'User is Not Authenticated!', 400);
            }
            $validator = Validator::make($request->all(), [
                'chat_room_id' => 'required',
                'reciever_id' => 'required',
                'type'=>'required',
            ]);
            if ($validator->fails()) {
                return customApiResponse(false, [$validator->errors()], 'Validation Error!', 400);
            }
            $input=$request->all();
            $chatRoom = PostChatRoom::where('id', $request->chat_room_id)->first();
            if (!$chatRoom) {
                return customApiResponse(false, [], 'Chat Room Not Found!', 400);
            }
            DB::beginTransaction();
            $category=PostChatMessage::create([
                'room_id'=>$input['chat_room_id'],
                'user_id'=>$user->id,
                'from_id'=>$input['reciever_id'],
                'message'=>$input['message']??"",
                'type'=>$input['type']
            ]);
            $destinationPath = public_path('postCategories');
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imageName);
                $category->link = 'public/postCategories/' . $imageName;
                $category->save();
            }
            DB::commit();
            event(new ChatEvent($input['reciever_id'], $category));
            return customApiResponse(true, [], 'Message Sent!');
        } catch (\Exception $e) {
            DB::rollBack();
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
    public function checkout(Request $request,$guid)
    {
        try {
            $product=PostProduct::where('guid',$guid)->first();
            if(!$product){
                return customApiResponse(false, [], 'Product Not Found!', 404);
            }
            $setting=AdminSetting::first();
            $price=$setting->promoted_price??2;
            Stripe::setApiKey(env('STRIPE_SK'));
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product->title,
                        ],
                        'unit_amount' => $price*100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'https://notnewbackendv2.testingwebsitelink.com/password/reset?session_id={CHECKOUT_SESSION_ID}&product_guid='.$product->guid,
                'cancel_url' =>"https://notnewbackendv2.testingwebsitelink.com/password/reset",
            ]);
            return customApiResponse(true, ["url"=>$checkoutSession->url], 'Checkout URL!');
        } catch (\Exception $e) {
            return customApiResponse(false, $e->getMessage(), 'Something Went Wrong', 500);
        }
    }
}