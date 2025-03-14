<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\TrustedSeller;
use Illuminate\Http\Request;

class TrustedSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('trusted-seller.index', ['user' =>
        // User::join('trusted_sellers', 'users.id', '=', 'trusted_sellers.user_id')
        //       ->orderBy('trusted_sellers.created_at', 'ASC')
        //     ->get()
        // ]);
        return view('trusted-seller.index', ['user' =>
        User::where('isTrustedSeller', true)
              ->orderBy('created_at', 'ASC')
            ->get()
        ]);
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
        // return view('trusted-seller.detail', ['user' =>
        // TrustedSeller::where('user_id', $id)
        //     ->first()
        // ]);
            return view('trusted-seller.detail', ['user' =>
        User::where('id', $id)
        ->with('shop')
            ->first()
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user = User::where('id', $id);
        $user->update(["isTrustedSeller" => $request->get('status')]);
        return back()->with('success', "Status Changed");
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
    public function changeUser(Request $request, $id)
    {
       $user = TrustedSeller::where('user_id', $id);
       $user->update(["percentage" => $request->get('percentage')]);
       return back()->with('success', "Updated");
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

    //this will show products associated with the user
    public function showUserProducts(User $user, Request $request)
    {
        $userProducts = User::with('products')->where('id', $user->id)->first();
        return view('customer.user-products', ['customer' => User::where('id', $user->id)->first(), 'customerProduct' => $userProducts->products,
            'active' => Product::where('user_id', $user->id)->select(['active'])->first()]);
    }

    public function showUserServices(User $user, Request $request)
    {
        $userServices = User::with('services')->where('id', $user->id)->first();
        return view('customer.user-services', ['customer' => User::where('id', $user->id)->first(), 'customerServices' => $userServices->services,
            'active' => Service::where('user_id', $user->id)->select(['active'])->first()]);
    }

    public function activateAllProducts(Request $request, User $user)
    {
        Product::where('user_id', $user->id)->update(['active' => $request->get('checkbox')]);
        return back()->with('success', "All Products Activated Of this user {$user->name}");
    }

    public function activateAllServices(Request $request, User $user)
    {
        Service::where('user_id', $user->id)->update(['active' => $request->get('checkbox')]);
        return back()->with('success', "All Services Activated Of this user {$user->name}");
    }

}
