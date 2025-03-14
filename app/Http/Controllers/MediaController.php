<?php

namespace App\Http\Controllers;

use App\Helpers\GuidHelper;
use App\Helpers\StringHelper;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('media.listing', ['media' => Media::where('system', true)->get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('media.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        $extension = StringHelper::lower($file->getClientOriginalExtension());
        $guid = GuidHelper::getGuid();
        $name = "site-content/{$guid}.{$extension}";
        $media = new Media();
        $media->fill([
            'name' => $name,
            'extension' => $extension,
            'type' => 'site-content',
            'user_id' => \Auth::user()->id,
            'active' => true,
            'system' => true,
            'guid' => $guid
        ]);

        $media->save();
        Storage::putFileAs(
            'public/site-content', $request->file('file'), "{$guid}.{$extension}"
        );
        return redirect('admin/media')->with('success', 'image upload' . $media->url);
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
    public function edit(Media $media)
    {
        //
        return view('media.edit', [
            'media' => $media,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Media $media)
    {
        //
        if ($request->hasFile('file'))
            return \DB::transaction(function () use ($media, $request) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                if (StringHelper::lower($extension) !== StringHelper::lower($media->extension)) {
                    return back()->with('error', 'use the same image extension please');
                }
                $guid = $media->guid;
                $name = "site-content/{$guid}.{$extension}";
                $media->fill([
                    'name' => $name,
                    'extension' => $extension,
                    'type' => 'site-content',
                    'user_id' => \Auth::user()->id,
                    'active' => true,
                    'system' => true,
                ]);

                Storage::disk('public')->delete($media->name);
                Storage::putFileAs(
                    'public/site-content', $request->file('file'), "{$guid}.{$extension}"
                );
                $media->save();
                return back()->with('success', 'successfully update');
            });

        return back()->with('error', 'no file attach');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy(Media $media)
    public function destroy($id)
    {
        $media = Media::where('id',$id)->first();
        Storage::disk('public')->delete($media->name);
        $media->delete();
        return back()->with('success', ' delete successfully');
    }
}
