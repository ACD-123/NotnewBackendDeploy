<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('unit-types.index', ['unitTypes' =>
            UnitType::where($this->applyFilters($request))
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
        //
        return view('unit-types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $unitType = new UnitType();
        $unitType->guid = GuidHelper::getGuid();
        $unitType = $unitType->fill(['name' => $request->get('name')]);
        $unitType->save();
        return redirect('admin/unit-type')
            ->with('success', 'Unit Type Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        return view('unit-types.edit',['unitType' => UnitType::find($id)]);
    }

    /**
     * @param Request $request
     * @param UnitType $unitType
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, UnitType $unitType)
    {
        $unitType->active = $request->active;
        $unitType->update($request->all());
        return back()->with('success',"{$unitType->name} Updated");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(UnitType $unitType)
    {
        //
        $unitType->delete();
        return back()->with('success',"{$unitType->name} Deleted");
    }
}
