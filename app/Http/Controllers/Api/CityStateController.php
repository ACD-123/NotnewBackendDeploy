<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use App\Models\Countries;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CityStateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $value = request()->get('value');

        return City::distinct('name')
            ->orderBy('name')
            ->get();

//        return City::orderBy('name')->limit(500)->get();
//        return DB::table('cities')->distinct()->get(['name']);
    }

    public function getState()
    {
        // dd('sss');
        return State::orderBy('id')->get();
    }

    public function getCountries()
    {
        // dd('sss');
        return Countries::orderBy('id')->get();
    }

    public function getCityStatebyPostal($zipcode)
    {
        return Address::getDatabyApi($zipcode);
    }
    public function getStateByCountry($id)
    {
        // dd('sss');
        return State::where('country_id', $id)->get();
    }
    public function getCityByStates($id)
    {
        // dd('sss');
        return City::where('state_id', $id)->get();
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
