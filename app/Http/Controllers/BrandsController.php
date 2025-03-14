<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use Illuminate\Support\Facades\DB;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.brand.index', [
            'brands' => Brands::orderBy('created_at','DESC')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.brand.form');
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('brands.index', ['brands' =>
            Brands::where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('brands.in-active', ['brands' => Brands::where('name', 'like', '%' . $search . '%')
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
            $bank = new Brands();
            $bank->guid = GuidHelper::getGuid();
            $bank->fill($request->all())->save();
            return redirect('admin/brands')->with('success', 'Brand Added.');
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
        return view('pages.brand.edit', ['brands' => Brands::findOrFail($id)]);
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
       
        $brands=Brands::findOrFail($id);
        $brands->fill($request->all())->update();
        return redirect('admin/brands')->with('success', 'Brand Updated.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $brands = Brands::where('id', $id)->delete();
        return redirect('admin/brands')->with('success', 'Brand Deleted.');
    }
}
