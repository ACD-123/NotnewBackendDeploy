<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use App\Models\Category;
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
        return Brands::get();    
    }

    public function withCategory(Request $request, $id)
    {
        // return Brands::with('category')
        //     ->where('id', $id)
        //     ->first();
          $category = Category::where('id', $id)                
        ->first();
        return Brands::where('category_id', $category->id)
            ->get(); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('brands.create');
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
        return view('brands.edit', ['brands' => Brands::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brands $brands)
    {
        // $data =[
        //     'name' => $request->get('name'),
        //     'category_id' =>  $request->get('category_id')
        // ];
        // $brands->name = $request->get('name');
        // $brands->category_id = $request->get('category_id');
        // $brands->update();
        $brands->fill($request->all())->update();
        return back()->with('success', 'Brand Updated');
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
        return back()->with('success', 'Brand deleted');
    }
}
