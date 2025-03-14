<?php

namespace App\Http\Controllers;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Models\Product;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bank.index', ['bank' =>
                Bank::orderBy('created_at', 'DESC')
                ->paginate(10)]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('bank.create');
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('bank.index', ['bank' =>
            Bank::where('fullname', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
    

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('bank.in-active', ['bank' => Bank::where('fullname', 'like', '%' . $search . '%')
            ->paginate(10)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $bank = new Bank();
            $bank->guid = GuidHelper::getGuid();
            $bank->fill($request->all())->save();
            return redirect('admin/banks')->with('success', 'Bank Added.');
        });
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('banks.show', ['bank' => Bank::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('bank.edit', ['bank' => Bank::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank $bank)
    {
        $bank->fill($request->all())->update();
        return back()->with('success', 'Bank Updated');
    }

    public function activateAll()
    {
        Bank::query()->update(['active' => 1]);
        return back()->with('success', 'All Categories Activated');
    }

    /**
     * @param Bank $bank
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @todo check the change please this is how would you bind the model
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();
        return back()->with('success', 'Bank deleted');
    }

    /**
     * showing the view of add properties
     * @param Bank $bank
     */
    public function showAttributes(Bank $bank)
    {
        
        return view('bank.add-properties',
            [  'bank' => $bank,
                'attributes' => Attribute::getAll()->get()
            ]
        );
    }

    public function addAttributes(Bank $bank, Request $request)
    {
        $categoryAttributes = new CategoryAttributes($request->all());

        $bank->categoryAttributes()->saveMany([$categoryAttributes]);
        // return back()->with('success', 'All Categories Activated');
        return view('bank.show-properties', ['bank' => $bank]);
    }

    public function showAttributesList(Bank $bank)
    {
        return view('bank.show-properties', ['bank' => $bank]);
    }

    public function attributes(Bank $bank, ?Product $product)
    {
        $defaults = [];
        if ($product->exists) {
            // @TODO: create relations to avoid where query
            $defaults = ProductsAttribute::where('product_id', $product->id)
                ->pluck('value', 'attribute_id')
                ->all();
        }
        // return $bank->attributes()->get();
        return view('products.attributes', ['attributes' => $bank->attributes()->get(), 'defaults' => $defaults]);
    }

    public function deleteCategoryAttribute($id)
    {
        $categoryAttribute = CategoryAttributes::where('id',$id)->first();
        $bank = Bank::where('id',$categoryAttribute->category_id)->first();
        CategoryAttributes::where('id',$id)
        ->delete();
        return view('bank.show-properties', ['bank' => $bank]);
    }

    public function searchCatAttributes(Bank $bank, Request $request)
    {
       $search = $request->get('search');
       $attribute = Attribute::where('name',$search)->first();
       $categoryAttribute = CategoryAttributes::where('attribute_id',$attribute->id)->first();
       $bank = Bank::where('id',$categoryAttribute->category_id)->first();
       return view('bank.show-properties', ['bank' =>$bank]);
    }
}
