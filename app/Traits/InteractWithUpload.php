<?php
/**
 * Created by Visual Code.
 * User: asad apextech
 * Date: 26/03/2024
 * Time: 10:40 PM
 */

namespace App\Traits;

use App\Helpers\ArrayHelper;
use App\Helpers\GuidHelper;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait InteractWithUpload
{
    public function uploadImage(Request $request, $entity)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $user="";
            $checkuser = $request->get('user_id');            
            if(\Auth::check()){
                $user = User::where('id', \Auth::user()->id)->first();
            }else {
                if($checkuser)
                {
                    $user = User::where('id', $request->get('user_id'))->first();
                }else{
                    $user = User::orderBy('id', 'desc')->first();
                }
            }
            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
            $name = "{$guid}.{$extension}";

            $media = new Media();

            $properties = [
                'name' => $name,
                'extension' => $extension,
                'type' => $entity::MEDIA_UPLOAD,
                'user_id' => $user->id,
                'active' => true,
            ];

            $entityProperty = [];

            if ($entity instanceof Product) {
                $entityProperty = ['product_id' => $entity->id];
            }
            // $media->fill(ArrayHelper::merge($properties, $entityProperty));
            // return ArrayHelper::merge($properties, $entityProperty);
            // die();
            $media->fill($properties);
            $media->save();
            // Storage::putFileAs(
            //     'public/' . $path, $request->file('file'), "{$guid}.{$extension}"
            // );
            // $path = public_path('images/'.$entity::MEDIA_UPLOAD.'/'.$entity->id);
            $path = 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id;
            $request->file->move($path, "{$guid}.{$extension}");
            return [
                'uid' => $media->id,
                'name' => 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}",
                'status' => 'done',
                'url' => 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}",
                'absolute_path'=> 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}"
            ];
        }
        throw new NotFoundHttpException('file not attach');
    }

    public function uploadImageCover(Request $request, $entity)
    {
        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $user="";
            $checkuser = $request->get('user_id');            
            if(\Auth::check()){
                $user = User::where('id', \Auth::user()->id)->first();
            }else {
                if($checkuser)
                {
                    $user = User::where('id', $request->get('user_id'))->first();
                }else{
                    $user = User::orderBy('id', 'desc')->first();
                }
            }
            $extension = $file->getClientOriginalExtension();
            $guid = GuidHelper::getGuid();
            // $path = User::getUploadPath($user->id) . $entity::MEDIA_UPLOAD;
            $name = "{$guid}.{$extension}";

            $media = new Media();

            $properties = [
                'name' => $name,
                'extension' => $extension,
                'type' => $entity::MEDIA_UPLOAD,
                'user_id' => $user->id,
                'active' => true,
            ];

            $entityProperty = [];

            if ($entity instanceof Product) {
                $entityProperty = ['product_id' => $entity->id];
            }
            // $media->fill(ArrayHelper::merge($properties, $entityProperty));
            // return ArrayHelper::merge($properties, $entityProperty);
            // die();
            $media->fill($properties);
            $media->save();
            // Storage::putFileAs(
            //     'public/' . $path, $request->file('file'), "{$guid}.{$extension}"
            // );
            // $path = public_path('images/'.$entity::MEDIA_UPLOAD.'/'.$entity->id);
            $path = 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id;
            $request->main_image->move($path, "{$guid}.{$extension}");
            return [
                'uid' => $media->id,
                'name' => 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}",
                'status' => 'done',
                'url' => 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}",
                'absolute_path'=> 'images/'.$entity::MEDIA_UPLOAD.'/'.$user->id.'/'."{$guid}.{$extension}"
            ];
        }
        throw new NotFoundHttpException('file not attach');
    }
}