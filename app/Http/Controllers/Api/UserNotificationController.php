<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserNotification::where('notifiable_id', Auth::user()->id)
               ->whereNull("read_at")
               ->orderByDesc('created_at')
               ->get();
    }

    public function count()
    {
        // return UserNotification::where('notifiable_id', Auth::user()->id)
        //        ->whereNull("read_at")
        //        ->get();
        return UserNotification::where('notifiable_id', Auth::user()->id)
               ->whereNull("read_at")
               ->count();
    }

    public function show($id,$viewmore)
    {
        // return UserNotification::where('notifiable_id', Auth::user()->id)
        //        ->whereNull("read_at")
        //        ->get();
        return UserNotification::where('notifiable_id', Auth::user()->id)
            //    ->whereNull("read_at")
            ->take($viewmore)
            ->orderby('id','DESC')
            ->get();
    }

    public function read()
    {
        return UserNotification::where('notifiable_id', Auth::user()->id)
            ->update([
            'read_at' => Carbon::now()->toDateTimeString(),]);
            return "All notification has been seen by User";
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
    // public function show($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        UserNotification::where('id', $id)->update($request->all());

        return $this->genericResponse(true, 'Notification Updated');;
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
