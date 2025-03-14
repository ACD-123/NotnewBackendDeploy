<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use Illuminate\Http\Request;
use Image;
use File;
use App\Models\Media;

class BannerController extends Controller
{
    
    public function index()
    {
        
        return view('pages.banner.index', [
            'banners' => Banner::with('media')->where('active',1)->where('featured',0)->orderBy('created_at','DESC')->get()
        ]);
    }

    public function indexFeatured()
    {
        
        return view('pages.banner.indexfeatured', [
            'banners' => Banner::with('media')->where('active',1)->where('featured',1)->orderBy('created_at','DESC')->get()
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.banner.form');
    }

    public function createFeatured()
    {
        return view('pages.banner.featuredform');
    }

    public function storeFeatured(Request $request)
    {
        
        return DB::transaction(function () use ($request) {
            $banner = new Banner();
            $banner->title =$request->title??"";
            $banner->url =$request->url??"";
            $banner->featured_until =date("Y-m-d h:i:s",strtotime($request->featured_until));
            $banner->type =$request->type;
            $banner->guid =$request->guid;
            $banner->active=1;
            $banner->featured=1;
            $banner->underage=$request->underage;
            $banner->save();
            if ($request->hasFile('image')) {
                
                
                $image = Image::make($request->file('image'));
               
                $imageName = time();
               
                $extension = $request->file('image')->getClientOriginalExtension();
                $destinationPath = public_path('image/category/');
               Image::make($request->file('image'))->resize(1024, 1024)->save('image/category/'.$imageName);
                
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $extension,
                    'type' => "banner",
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'banner_id'=> $banner->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect('featured-banner-table')->with('success', 'Banner Added.');
        });
    }

    public function store(Request $request)
    {
        
        return DB::transaction(function () use ($request) {
            $banner = new Banner();
            $banner->title =$request->name??"";
            $banner->underage=$request->underage;
            $banner->active =1;
            $banner->fill($request->all())->save();
            if ($request->hasFile('image')) {
                
                $image = Image::make($request->file('image'));
               
                $imageName = time();
               
                $extension = $request->file('image')->getClientOriginalExtension();
                $destinationPath = public_path('image/category/');
               Image::make($request->file('image'))->resize(1024, 1024)->save('image/category/'.$imageName);
                
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $extension,
                    'type' => "banner",
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'banner_id'=> $banner->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect('banner-table')->with('success', 'Banner Added.');
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
        return view('pages.banner.edit', ['banner' => Banner::with('media')->findOrFail($id)]);
    }

    public function editFeatured($id)
    {
        return view('pages.banner.editfeatured', ['banner' => Banner::with('media')->findOrFail($id)]);
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
        return DB::transaction(function () use ($request,$id) {
            $banner=Banner::find($id);
            $banner->underage=$request->underage;
            $banner->save();
            if ($request->hasFile('image')) {
                $media = Media::where('banner_id', $banner->id)->first();
                if($media){
                    $image_path = env("APP_URL")."image/category/". $media->name;  // Value is not URL but directory file path
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                    Media::where('banner_id', $banner->id)->delete();
                }
                $image = Image::make($request->file('image'));
               
                $imageName = time();
               
                $extension = $request->file('image')->getClientOriginalExtension();
                $destinationPath = public_path('image/category/');
               Image::make($request->file('image'))->resize(1024, 1024)->save('image/category/'.$imageName);
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $extension,
                    'type' => "banner",
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'banner_id'=> $banner->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect('banner-table')->with('success', 'Banner Updated.');
        });
    }

    public function updateFeatured(Request $request, $id)
    {
        return DB::transaction(function () use ($request,$id) {
            $banner=Banner::find($id);
            $banner->title =$request->title??$banner->title;
            $banner->url =$request->url??$banner->url;
            $banner->featured_until =date("Y-m-d h:i:s",strtotime($request->featured_until));
            $banner->type =$request->type??$banner->type;
            $banner->guid =$request->guid??$banner->guid;
            $banner->underage=$request->underage;
            $banner->save();
            if ($request->hasFile('image')) {
                $media = Media::where('banner_id', $banner->id)->first();
                if($media){
                    $image_path = env("APP_URL")."image/category/". $media->name;  // Value is not URL but directory file path
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                    Media::where('banner_id', $banner->id)->delete();
                }
                $image = Image::make($request->file('image'));
               
                $imageName = time();
               
                $extension = $request->file('image')->getClientOriginalExtension();
                $destinationPath = public_path('image/category/');
               Image::make($request->file('image'))->resize(1024, 1024)->save('image/category/'.$imageName);
                $media = new Media();
                $guid = GuidHelper::getGuid();
                $properties = [
                    'name' => $imageName,
                    'extension' => $extension,
                    'type' => "banner",
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'banner_id'=> $banner->id,
                ];
                $media->fill($properties);
                $media->save();
            }
            return redirect('featured-banner-table')->with('success', 'Banner Updated.');
        });
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $brands = Banner::where('id', $id)->update([
            "active"=>0
        ]);
        return redirect('banner-table')->with('success', 'Banner Deleted.');
    }

    public function destroyFeatured($id)
    {
        $brands = Banner::where('id', $id)->update([
            "active"=>0
        ]);
        return redirect('featured-banner-table')->with('success', 'Banner Deleted.');
    }
    
}
