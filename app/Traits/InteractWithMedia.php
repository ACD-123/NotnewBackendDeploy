<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/2021
 * Time: 3:40 AM
 */

namespace App\Traits;

use App\Models\Media;

trait InteractWithMedia
{
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function images()
    {
        return $this->media->map(function ($media) {
            return [
                'uid' => $media->id,
                'name' => $media->url,
                'status' => 'done',
                'url' => $media->url,
                'guid' => $media->guid
            ];

        });
    }
}
