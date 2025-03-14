<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaveCartLater;

class SaveCartLaterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SaveCartLater::get();
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
        //Delete Existing Record
        SaveCartLater::where('cart_id', $request->get('cart_id'))
            ->where('user_id', \Auth::user()->id)->delete();
        //Save New Record
        $saveforLater = new SaveCartLater();
        $saveforLater->cart_id = $request->get('cart_id');
        $saveforLater->user_id = \Auth::user()->id;
        $saveforLater->save();
        return response()->json([
            'message' => 'Cart Item has Been Added to Save Later'
        ]);
    }

    public function getById(Request $request, $id){
        return $id;
    }
    public function getByUser(Request $request){
        return SaveCartLater::where('user_id', \Auth::user()->id)
        ->with(['user'])
        ->with(['cart'])
        // ->where('cart_id', $request->get('product_id'))
        ->get();
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
