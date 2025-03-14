<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('pages.attribute.attribute', ['attributes' =>
            Attribute::where($this->applyFilters($request))
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
        return view('pages.attribute.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attribute = new Attribute();
        $attribute->guid = GuidHelper::getGuid();
        $attribute = $attribute->fill($request->all());
        $attribute->save();
        
        return redirect()->back()
            ->with('success', 'Attribute Added');
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
        return view('pages.attribute.edit', ['attribute' => Attribute::find($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attribute $attribute)
    {
        $attribute->update($request->all());
        return back()->with('success', "{$attribute->name} Updated");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', "{$attribute->name} Deleted");
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('attributes.index', ['attributes' =>
        Attribute::where('active', true)
                ->where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
}
