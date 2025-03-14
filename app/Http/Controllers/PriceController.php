<?php

namespace App\Http\Controllers;

use App\Models\Prices;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('prices.index', ['prices' =>
        Prices::where($this->applyFilters($request))
                ->orderBy('created_at', 'ASC')
                ->paginate($this->getPageSize())]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('prices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $prices = new Prices();
        $prices = $prices->fill($request->all());
        $prices->save();
        return redirect('admin/price')->with('success', 'Price Added.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        return view('prices.edit', ['prices' => Prices::find($id)]);
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
        $prices = Prices::where('id',$id)->first();
        $prices->fill($request->all())->update();
        return back()->with('success', "{$prices->name} Updated");
    }

    public function destroy($id)
    {
        $prices = Prices::where('id',$id)->first();
        $prices->delete();
        return back()->with('success', "{$prices->name} Deleted");
    }

    public function inActive()
    {
        return view('prices.in-active', ['prices' =>
            Prices::where('active', false)
                ->orderBy('created_at', 'ASC')
                ->paginate(10)]);
    }

    public function activateAll()
    {
        Prices::query()->update(['active' => 1]);
        return back()->with('success', 'All Prices Activated');
    }

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('prices.in-active', ['prices' => Prices::where('active', 0)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10)]);
    }
    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('prices.index', ['prices' =>
            Prices::where('active', true)
                ->where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
}
