<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageReceived;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MessageSent;

class MessageSentNotification
{
    public $name;
    public $email;
    public $sender;
    public $recipientId;
    public $productId;
    public function routeNotificationFor()
    {
        return $this->email;
    }
}

class MessageController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        // validation on recipient id

        $message = new Message();
        $message->sender_id = \Auth::user()->id;
        $message->recipient_id = $request->get('recipient_id');
        $message->data = $request->get('message');
        $message->notifiable_type = Product::class;
        $message->notifiable_id = $request->get('notifiable_id');
        $message->save();

        MessageReceived::trigger($user, $chat_id);

        return $this->genericResponse(true, 'Message sent successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function show($recipientId, $productId)
    {
        $user = Auth::user();
        $product_id = $productId;
        $chat_id;


        if ($user->id > $recipientId) {
            $chat_id = $recipientId.$user->id;
        } else {
            $chat_id = $user->id.$recipientId;
        }
        return Message::select('*')
            ->where("product_id", $product_id)
            ->where("chat_id", $chat_id)
            ->orderBy('created_at')
            ->paginate($this->pageSize);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }

    // public function conversations($product_id)
    public function conversations()
    {
        $authenticatedUserId = \Auth::user()->id;
        // $productId = $product_id;

        return Message::join('users as u1', 'u1.id', '=', 'messages.sender_id')
               ->join('users as u2', 'u2.id', '=', 'messages.recipient_id')
            //    ->where("product_id", $productId)
               ->distinct('chat_id')
               ->where('sender_id', $authenticatedUserId)
               ->orWhere('recipient_id', $authenticatedUserId)
               ->get([
                            'u1.id as sender_id',
                            'u1.name as sender_name',
                            'u1.profile_url as sender_image',
                            'u2.id as recipient_id',
                            'u2.name as recipient_name',
                            'u2.profile_url as recipient_image',
                            'messages.product_id as product',
                            'messages.data as message',
                ]);
            //    ->paginate($this->pageSize, [
            //         'u1.id as sender_id',
            //         'u1.name as sender_name',
            //         'u1.profile_url as sender_image',
            //         'u2.id as recipient_id',
            //         'u2.name as recipient_name',
            //         'u2.profile_url as recipient_image',
            //         'messages.product_id as product',
            //         'messages.data as message',
            //     ]);
    }

    public function getUserConversations()
    {
        $authenticatedUserId = \Auth::user()->id;

        return Message::join('users as u1', 'u1.id', '=', 'messages.sender_id')
               ->join('users as u2', 'u2.id', '=', 'messages.recipient_id')
               ->join('products as p', 'p.guid', '=', 'messages.product_id')
               ->where("sender_id", $authenticatedUserId)
               ->orWhere("recipient_id", $authenticatedUserId)
               ->distinct('product_id')
               ->get([
                'u1.id as sender_id',
                'u1.name as sender_name',
                'u1.profile_url as sender_image',
                'u2.id as recipient_id',
                // 'messages.product_id as product',
                'u2.name as recipient_name',
                'u2.profile_url as recipient_image',
                'messages.data as description',
                // 'p.name as product_name',
            ]);
            //    ->paginate($this->pageSize, [
            //         'u1.id as sender_id',
            //         'u1.name as sender_name',
            //         'u1.profile_url as sender_image',
            //         'u2.id as recipient_id',
            //         'messages.product_id as product',
            //         'u2.name as recipient_name',
            //         'u2.profile_url as recipient_image',
            //         'p.name as product_name',
            //     ]);
    }

    public function saveAssociated(Request $request)
    {
        return DB::transaction(function () use ($request) {  
            $user = Auth::user();
            $id = $request->get("recipient_id");
            $recipientUser = \App\Models\User::find($id);

            if ($user->id > $request->get("recipient_id")) {
                $chat_id = $request->get("recipient_id").$user->id;
            } else {
                $chat_id = $user->id.$request->get("recipient_id");
            }

            $message_sent = new MessageSentNotification();
            $message_sent->name = $recipientUser->name;
            $message_sent->email = $recipientUser->email;
            $message_sent->sender = $user->name;
            $message_sent->recipientId = $user->id;
            $message_sent->productId = $request->get("product_id");
            // $recipientUser->notify(new MessageSent($message_sent));
            $data = [
                'sender_id' => $request->get('sender_id'),
                'recipient_id' => $request->get("recipient_id"),
                'chat_id' => $chat_id,
                'product_id' => $request->get("product_id"),
                'data' => $request->get("data"),
                'notifiable_type' => '\App\Models\Product',
                'notifiable_id' => $request->get("recipient_id")
            ];
            $message = Message::create($data);

            // MessageReceived::trigger($recipientUser, $chat_id, $request->get("product_id"));
            if($message){
                return response()->json(['status'=>'true','data'=>'Message Created Successfully!'],200);
            }else{
                return response()->json(['status'=>'false','message'=>$product],500);
            }
    
        });

    }

    public function getCount(Request $request)
    {
        return Message::getCount();
    }

    public function getNotifications(Request $request)
    {
        return Message::getNotifications();
    }
}
