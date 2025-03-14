<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Faq;
use App\Models\Help;
use Mail;
use Image;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $status = $request->type;
            $data = Faq::where('status', $status)->get();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Fetched Data Successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Something Went Wrong!'
            ], 400);
        }

    }
    
    public function store(Request $request)
    {
        try {
            $help=Help::create([
                "user_id"=>$request->user_id,
                "message"=>$request->message,
                "subject"=>$request->subject
            ]);
            $imagesArray=[];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $image = Image::make($file);
                    $imageName = time().'-'.$file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $destinationPath = public_path('image/category/');
                    Image::make($file)->resize(1024, 1024)->save('image/category/'.$imageName);
                    $imageVal=url('/').'/image/category/'.$imageName;
                    array_push($imagesArray,$imageVal);
                }
                 
            }

            $help->update([
                "image"=>implode(",",$imagesArray)
            ]);
               
            
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Fetched Data Successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 400);
        }

    }
}