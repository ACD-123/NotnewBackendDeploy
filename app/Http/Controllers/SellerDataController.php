<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use Illuminate\Http\Request;
use App\Models\SellerData;
use App\Models\User;
use App\Models\Media;
use App\Models\Feedback;
use App\Models\UserBank;
use App\Models\SaveSeller;
use App\Models\Bank;
use App\Http\Requests\SellerDataRequest;
use App\Notifications\SellerDataNotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\ArrayHelper;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServicesCategories;
use App\Notifications\serviceApproved;
use App\Notifications\ServicesRejected;
use Carbon\Carbon;
use App\Images;
use Image;
use App\Models\ReportSeller;

class SellerDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sellerdata.index', ['sellerdata' =>
        SellerData::with('user')
        ->paginate(10)]);
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('sellerdata.edit', ['sellerdata' => SellerData::find($id)]);
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
        $sellerData = SellerData::where('id', $id)
        ->update([
        'featured' => $request->get('featured'),
        'active' => $request->get('active'),
        ]);
        return redirect('admin/sellerdata')->with('success', 'You have SuccessFully Update Shop Data!');
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('sellerdata.index', ['sellerdata' =>
            SellerData::where('fullname', 'like', '%' . $search . '%')
                ->paginate(10)]);
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
    public function sellerReport(Request $request,$guid)
    {
        $sellerData= SellerData::with('user')->where('guid',$guid)->first();
        $reports=ReportSeller::with('user')->where('seller_guid',$guid)->get();
         return view('sellerdata.report',compact("sellerData","reports")); 
    }
}
