<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use App\Helpers\GuidHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) { 
            $stocks = Stock::where('user_id',Auth::user()->id)
            ->where('product_id', $request->product_id)->first();
            $quantity = 0;
            if($stocks){
                $quantity= $stocks->quantity + $request->quantity;    
                Stock::where('user_id',Auth::user()->id)
                ->where('product_id', $request->product_id)
                 ->update([
                        "quantity"=>$quantity
                    ]);
                    $product = Product::where('id', $request->product_id)
                    ->update([
                        "stockcapacity"=>$quantity
                        ]);
                                        if($product){
                    return response()->json(['status'=> true,'data' =>"Product Stock has been Created!"], 200);       
                }else{
                    return response()->json(['status'=> false,'data' =>"Unable To Submit Order!"], 400);       
                }

            }else{
                $quantity= $request->quantity;
                $stock = new Stock();
                $stock->user_id = Auth::user()->id;  
                $stock->product_id = $request->product_id;  
                $stock->quantity = $quantity;  
                $stock->guid = GuidHelper::getGuid();
                $stock->save();
                
                $product = Product::where('id', $request->product_id)
                    ->update([
                        "stockcapacity"=>$quantity
                        ]);
                if($product){
                    return response()->json(['status'=> true,'data' =>"Product Stock has been Created!"], 200);       
                }else{
                    return response()->json(['status'=> false,'data' =>"Unable To Submit Order!"], 400);       
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
