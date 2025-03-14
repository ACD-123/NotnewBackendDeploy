<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNotificationModel;
use App\Models\Product;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Notification::where('notifiable_id', Auth::user()->id)
            ->whereNull("read_at")
            ->orderByDesc('created_at')
            ->get();
    }

    public function count()
    {
        return Notification::where('notifiable_id', Auth::user()->id)
            ->whereNull("read_at")
            ->count();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Notification::where('id', $id)->update($request->all());

        return $this->genericResponse(true, 'Notification Updated');
        ;
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
    public function getUserNotification(Request $request)
    {
        try {
            $user = Auth::user()->id ?? $request->user_id;
            $total = UserNotificationModel::where('user_id', $user)->where('type', $request->type)->count();
            $count = UserNotificationModel::where('user_id', $user)->count();
            
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
            $notifications = UserNotificationModel::with(['user','sender'])->where('user_id', $user)->where('type', $request->type)->orderBy('id', 'DESC')->skip($skip)->take($page_size)->get();
            if($request->type=="auction"){
                foreach ($notifications as $value) {
                    $product=Product::where('guid',$value->product_guid)->first();
                    $value->auction_status=1;
                    if($product->is_sold==1)
                    {
                        $value->auction_status=0;
                    }
                    $currentTime = Carbon::now();
                    $auctionEndTime = Carbon::parse($product->auction_End_listing);
                    if ($auctionEndTime->lessThan($currentTime)) {
                        $value->auction_status=0;
                    }
                }
                
            }
            UserNotificationModel::where('user_id', $user)->where('type', $request->type)->update([
                "is_read"=>1
            ]);
            $data = [
                "notifications" => $notifications,
                "count"=>$count,
                "pagination" => $pagination
            ];
            return response()->json(['status' => true, 'data' => $data], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }

    }
    public function getUserNotificationCount(Request $request)
    {
        try {
            $user =  $request->user_id;
            $total = UserNotificationModel::where('user_id', $user)->where('is_read',0)->count();
            $important = UserNotificationModel::where('user_id', $user)->where('type', 'important')->where('is_read',0)->count();
            $buying = UserNotificationModel::where('user_id', $user)->where('type', 'buying')->where('is_read',0)->count();
            $selling = UserNotificationModel::where('user_id', $user)->where('type', 'selling')->where('is_read',0)->count();
            $auction = UserNotificationModel::where('user_id', $user)->where('type', 'auction')->where('is_read',0)->count();
            $chats = UserNotificationModel::where('user_id', $user)->where('type', 'chats')->where('is_read',0)->count();
            $data = [
                "total" => $total,
                "important"=>$important,
                "buying" => $buying,
                "selling" => $selling,
                "auction" => $auction,
                'chats'=>$chats
            ];
            return response()->json(['status' => true, 'data' => $data], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
