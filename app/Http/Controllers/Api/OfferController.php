<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\User;
use App\Models\Product;
use App\Models\Message;
use App\Models\UserOfferProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfferStatusNotification;
use App\Events\OfferStatusEvent;

class OfferStatus
{
    public $email;
    public $name;
    public $status;
    public function routeNotificationFor()
    {
        return $this->requester->email;
    }
}

class OfferController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
     
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function statusHandler(Request $request, Offer $offer)
    {
        $chat_id;
        $offer_status = new OfferStatus();
        $status = $request->get('status') ? Offer::$STATUS_ACCEPT : Offer::$STATUS_REJECT;
        $offer->update(["status_name" => $status]);
        $requester = User::where('id', $offer->requester_id)->first();
        $user = User::where('id', $offer->user_id)->first();
        $product = Product::where('id', $offer->product_id)->first();
        $offer_status->email = $requester->email;
        $offer_status->name = $user->name;
        $offer_status->status = $request->get('status') ? 1 : 0;
        $userofferproduct = new UserOfferProduct();
        $userofferproduct->update(["status" => '0']);
        $requester->notify(new OfferStatusNotification($offer_status));

        OfferStatusEvent::trigger($requester);

        if ($user->id > $offer->requester_id) {
            $chat_id = $offer->requester_id.$user->id;
        } else {
            $chat_id = $user->id.$offer->requester_id;
        }

        $message = new Message();
        $message->sender_id = $user->id;
        $message->product_id = $product->guid;
        $message->chat_id = $chat_id;
        $message->recipient_id = $offer->requester_id;
        $message->data = $request->get('status') ? $user->name . ' has accepted your offer of ' . $offer->price . ' for ' . $product->name : $user->name . ' has cancelled the offer';
        $message->notifiable_id = $product->id;
        $message->notifiable_type = Product::class;
        $message->save();

        return $this->genericResponse(true, "request updated");
    }

    public function pendingOffer($id){
        return Offer::where('id', $id)->with(["product" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::defaultSelect());
        } , "requester" => function (BelongsTo $hasMany) {
            $hasMany->select(User::defaultSelect());
        }, "user" => function (BelongsTo $hasMany) {
            $hasMany->select(Product::getUser());
        }])->get();
    }

    public function cancelOffer($id){
        Offer::cancelOffer($id);
        return $this->genericResponse(true, "Offer Canceled");
    }

    
}
