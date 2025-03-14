<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\ArrayHelper;
use App\Helpers\StringHelper;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductsAttribute;
use App\Models\ProductsCategories;
use App\Scopes\ActiveScope;
use App\Scopes\SoldScope;
use App\Notifications\AdApproved;
use App\Notifications\AdRejected;
use App\Notifications\OfferStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Notification;
use App\Models\Favourite;
use App\Models\Wishlist;
use App\Models\UserOrderDetails;
use App\Models\Bidding;
use App\Models\UserCart;


class OfferStatus
{
    public $email;
    public $name;
    public $status;
    public function routeNotificationFor()
    {
        return $this->requester->email;
    }
}
class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.product.product', [
            'products' => Product::orderBy('created_at', 'DESC')->paginate(16) 
        ]);
    }

    public function inActive()
    {
        // $inActiveProduct = Product::where('active', false)->where('IsSaved',true)->paginate(10);
        // echo "<pre />";
        // print_r($inActiveProduct);
        // die();
        // // dd($inActiveProduct);
        // // $inActiveProduct = ProductsCategories::with('products')
        // //     ->whereHas('products', function ($query) {
        // //         $query->where('active', false);
        // //     })->where('IsSaved',true)->orderBy('created_at', 'ASC')->paginate(10);
        // return view('products.in-active', ['products' => $inActiveProduct]);
                                                        // return Product::where('active', false)
                                                        // ->where('IsSaved',true)
                                                        // ->orderBy('created_at', 'DESC')
                                                        // ->paginate(10); 
        return view('products.in-active ', [
            'products' => Product::where('active', false)
            ->where('IsSaved',true)
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
        ]);
        // view('products.in-active', ['product' =>
        // Product::where('active', false)
        //     ->where('IsSaved',true)
        //     ->orderBy('created_at', 'DESC')
        //     ->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create', ['categories' => Category::where('active', true)->with('attributes')->get()]);
    }

    public function search(Request $request)
    {
        //  \Artisan::call('optimize');
        //  die();
        $search = $request->get('search');
        $products = Product::withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)
            ->where('name', 'like', '%' . $search . '%')
            // ->where('IsSaved',true)
            ->paginate(15);
        return view('products.index', ['products' => $products]);
    }

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        $products = Product::withoutGlobalScope(ActiveScope::class)
        ->withoutGlobalScope(SoldScope::class)
        ->where('name', 'like', '%' . $search . '%')
        ->where('active', false)
        ->where('IsSaved',true)
        ->paginate(15);
        // dd($products);
        // $products = Product::where('active', false)->where('name', 'like', '%' . $search . '%')->paginate(10);
        // $products = ProductsCategories::with('products')
        //     ->whereHas('products', function ($query) use ($search) {
        //         $query->where('active', false)->where('name', 'like', '%' . $search . '%');
        //     })->withoutGlobalScope(ActiveScope::class)
        //     ->withoutGlobalScope(SoldScope::class)->paginate(10);
        return view('products.in-active', ['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        
        DB::transaction(function () use ($request) {
            $product = new Product();
            $product->guid = GuidHelper::getGuid();
            $request['user_id'] = auth()->user()->getAuthIdentifier();
            $product->fill(ArrayHelper::merge($request->all(),
                [
                    'status' => 'DRAFT',
                    'IsSaved'=>true
                ]
            ))->save();
            // $product->fill($request->all())->save();
           
            $attributes = [];
            foreach ($request->get('attributes', []) as $id => $value) {
                $attributes[] = [
                    'attribute_id' => $id,
                    'product_id' => $product->id,
                    'value' => $value
                ];
            }

            ProductsAttribute::insert($attributes);
        });
        return redirect('admin/products')->with('success', 'Product Added');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $media =  Media::where('product_id',$id)->first();
        return view('products.edit', [
            'product' => Product::with('category')->withoutGlobalScope(ActiveScope::class)
                ->withoutGlobalScope(SoldScope::class)->findOrFail($id),
            'category' => Category::where('active', true)->get(),
            'media' =>Media::where('product_id',$id)->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        
        if ($request->get('activateOne') == "activateOnlyOne") {
           
            $product->update(['active' => !empty($request->get('checkbox')), 'in_review' => false]);
            $user = User::where('id', $product->user_id)->first();
            $offer_status = new OfferStatus();
            $offer_status->email = $user->email;
            $offer_status->name = $user->name;
            $offer_status->status = $request->get('checkbox') ? 1 : 0;
            if(!empty($request->get('checkbox'))){
                if($request->get('checkbox') == true){
                    $notify =  $user->notify(new AdApproved($offer_status, $product));
                }
            }else{
                if($request->get('checkbox') == false){
                    $notify =  $user->notify(new AdRejected($offer_status, $product));
                }
            }
            return back()->with('success', "{$product->name} Status Changed Successfully.");
        } else {
            
            $product->fill($request->all())->update();
            $product->in_review = false;
            $product->update();
            //  ProductsCategories::where('product_id', $product->id)->update(['category_id' => $request->category_id]);
            $attributes = $request->get('attributes', []);
            // @TODO: create relations to avoid where query
            ProductsAttribute::where('product_id', $product->id)
                ->get()
                ->each(function (ProductsAttribute $attribute) use ($attributes) {
                    $attribute->value = $attributes[$attribute->attribute_id];
                    $attribute->save();
                });
            $user = User::where('id', $product->user_id)->first();
            $offer_status = new OfferStatus();
            $offer_status->email = $user->email;
            $offer_status->name = $user->name;
            $offer_status->status = $request->get('status') ? 1 : 0;
            if($request->get('status') == true){
                $notify =  $user->notify(new AdApproved($offer_status, $product));
            }
            return redirect('admin/products')->with('success', 'Product Updated');
        }
    }
    
    public function updateHot(Request $request, $id)
    {
        $product=Product::find($id);
        //dd($product->id);
        $product->update(['hot' => $product->hot ? 0 : 1]);
        return back()->with('success', "{$product->name} Hot Status Changed Successfully.");
    }

    public function activateAll()
    {

        Product::query()->withoutGlobalScope(ActiveScope::class)
            ->withoutGlobalScope(SoldScope::class)->update(['active' => 1]);
        return back()->with('success', 'All Products Activated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        Favourite::where('favourite_against_id',$product->guid)->delete();
        Wishlist::where('product_id',$product->id)->delete();
        UserOrderDetails::where('product_id',$product->id)->delete();
        Bidding::where('product_id',$product->id)->delete();
        UserCart::where('product_id',$product->id)->delete();
        $product->delete();
        return back()->with('success', 'Product Deleted');
    }

    public function activeInactive ($id){
        $product=Product::find($id);
       
        $product->update(['is_deleted' => $product->is_deleted ? 0 : 1]);
        return back();
    }

    public function isFeaturetActiveInactive ($id){
        $product=Product::find($id);
        $product->update(['featured' => $product->featured ? 0 : 1]);
        return back();
    }
}
