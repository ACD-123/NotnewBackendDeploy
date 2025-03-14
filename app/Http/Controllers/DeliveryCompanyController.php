<?php

namespace App\Http\Controllers;

use App\Models\DeliverCompany;
use Illuminate\Support\Facades\DB;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use Illuminate\Http\Request;

class DeliveryCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('deliverycompany.index', [
            'deliverycompany' => DeliverCompany::paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('deliverycompany.create');
    }
    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('deliverycompany.index', ['brands' =>
            DeliverCompany::where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('deliverycompany.in-active', ['deliverycompany' => DeliverCompany::where('name', 'like', '%' . $search . '%')
            ->paginate(10)]);
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
            $deliverycompany = new DeliverCompany();
            $deliverycompany->guid = GuidHelper::getGuid();
            $deliverycompany->fill($request->all())->save();
            return redirect('admin/deliverycompany')->with('success', 'Delivery Company Added.');
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
        return view('deliverycompany.edit', ['deliverycompany' => DeliverCompany::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliverCompany $delivercompany)
    {
        $delivercompany->fill($request->all())->update();
        return back()->with('success', 'Deliver Company Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delivercompany = DeliverCompany::where('id', $id)->delete();
        return back()->with('success', 'DeliverCompany deleted');
    }
}
