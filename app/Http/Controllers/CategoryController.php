<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Http\Requests\CategoryRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryAttributes;
use App\Models\Product;
use App\Models\Media;
use App\Models\ProductsAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use File;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.category', ['category' =>
            Category::where('active', true)
                ->with(['media'])
                ->orderBy('created_at', 'DESC')->get()]);
    }

    public function inActive()
    {
        return view('category.in-active', ['category' =>
            Category::where('active', false)
                ->orderBy('created_at', 'DESC')
                ->paginate(10)]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $categories = Category::where('active', true)->get();

        return view('pages.category.form', ['categories' => $categories]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        return view('category.index', ['category' =>
            Category::where('active', true)
                ->where('name', 'like', '%' . $search . '%')
                ->paginate(10)]);
    }
    

    public function searchInActive(Request $request)
    {
        $search = $request->get('search');
        return view('category.in-active', ['category' => Category::where('active', false)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(10)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $category = new Category();
            $category->guid = GuidHelper::getGuid();
            $category->fill($request->all())->save();
            if ($request->hasFile('file')) {
                
                $image = Image::make($request->file('file'));
                $imageName = time().'.'.$request->file('file')->getClientOriginalExtension();
            //    dd($imageName);
                // $extension = $request->file('file')->getClientOriginalExtension();
                $destinationPath = public_path('image/category/');
               Image::make($request->file('file'))->resize(768, 768)->save('image/category/'.$imageName);
                // $image->resize(1024, 1024, function ($constraint) {
                //     $constraint->aspectRatio();
                //     $constraint->upsize();
                // });
                // $image->save($destinationPath.$imageName);
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $request->file('file')->getClientOriginalExtension(),
                    'type' => Category::MEDIA_UPLOAD,
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'category_id'=> $category->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect()->route('categoryTable')->with('success', 'Category Added.');
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
        return view('category.show', ['category' => Category::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::where('active', true)->get();
        return view('pages.category.edit', ['category' => Category::with(['media'])->findOrFail($id), 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        return DB::transaction(function () use ($request, $category) {
            $category->fill($request->all())->update();
            if ($request->hasFile('file')) {
                $media = Media::where('category_id', $category->id)->first();
                if($media){
                    $image_path = "https://notnewbackend.testingwebsitelink.com/image/category/". $media->name;  // Value is not URL but directory file path
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                    Media::where('category_id', $category->id)->delete();
                }
                $image = Image::make($request->file('file'));
                $imageName = time().'.'.$request->file('file')->getClientOriginalExtension();
                $string = str_replace(' ', '-', $imageName); // Replaces spaces with hyphens.
                // $imageNames=  preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
                $extension = $request->file('file')->getClientOriginalExtension();
                // $destinationPath = public_path('image/category/');
               
                // $image->resize(1024, 1024, function ($constraint) {
                //     $constraint->aspectRatio();
                //     $constraint->upsize();
                // });
                //$image->save($destinationPath.$imageName);
                Image::make($request->file('file'))->resize(768, 768)->save('image/category/'.$imageName);
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $extension,
                    'type' => Category::MEDIA_UPLOAD,
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'category_id'=> $category->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect()->route('categoryTable')->with('success', 'Category Updated.');
        });
        // if ($request->get('activateOne') == "activateOnlyOne") {
        //     $category->update(['active' => StringHelper::isValueTrue($request->get('active'))]);
        //     return back()->with('success', "{$category->name} Activated Successfully.");
        // }
        // $category->fill($request->all())->update();
        // return back()->with('success', 'Category Updated');
    }

    public function activateAll()
    {
        Category::query()->update(['active' => 1]);
        return back()->with('success', 'All Categories Activated');
    }

    /**
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @todo check the change please this is how would you bind the model
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    /**
     * showing the view of add properties
     * @param Category $category
     */
    public function showAttributes(Category $category)
    {
        
        return view('category.add-properties',
            [  'category' => $category,
                'attributes' => Attribute::getAll()->get()
            ]
        );
    }

     public function addAttributes(Category $category, Request $request)
    {
        $this->validate($request, [
            'unit_type_id' => 'required'
        ],[
            'unit_type_id.required' => 'Please Enter Unit Type'
        ]);
        $categoryAttributes = new CategoryAttributes($request->all());

        $category->categoryAttributes()->saveMany([$categoryAttributes]);
        // return back()->with('success', 'All Categories Activated');
        return view('category.show-properties', ['category' => $category]);
    }


    public function showAttributesList(Category $category)
    {
        return view('category.show-properties', ['category' => $category]);
    }

    public function attributes(Category $category, ?Product $product)
    {
        $defaults = [];
        if ($product->exists) {
            // @TODO: create relations to avoid where query
            $defaults = ProductsAttribute::where('product_id', $product->id)
                ->pluck('value', 'attribute_id')
                ->all();
        }
        // return $category->attributes()->get();
        return view('products.attributes', ['attributes' => $category->attributes()->get(), 'defaults' => $defaults]);
    }

    public function deleteCategoryAttribute($id)
    {
        $categoryAttribute = CategoryAttributes::where('id',$id)->first();
        $category = Category::where('id',$categoryAttribute->category_id)->first();
        CategoryAttributes::where('id',$id)
        ->delete();
        return view('category.show-properties', ['category' => $category]);
    }

    public function searchCatAttributes(Category $category, Request $request)
    {
       $search = $request->get('search');
       $attribute = Attribute::where('name',$search)->first();
       $categoryAttribute = CategoryAttributes::where('attribute_id',$attribute->id)->first();
       $category = Category::where('id',$categoryAttribute->category_id)->first();
       return view('category.show-properties', ['category' =>$category]);
    }
    public function showCategoryAttributes($id)
    {
        $category = Category::where('id',$id)->first();
         $attributes = Attribute::where('active',1)->get();
         $categoryAttributes = CategoryAttributes::where('category_id',$id)->get();
         return view('category.editcategoryattribute',compact('category','attributes','categoryAttributes'));
    }
    
    public function addOrUpdateCategoryAttributes(Request $request)
    {
       
        CategoryAttributes::where('category_id',$request->category_id)->delete();
        $data=$request->all();
        if(isset($data['attributes'])){
            foreach($data['attributes'] as $attr){
                
           CategoryAttributes::create([
           "attribute_id"=>$attr,
           "category_id"=>$request->category_id
           ]); 
            }
           
        }
         return redirect('admin/category')->with('success', 'Category Attributes Updated.');
    }
}
