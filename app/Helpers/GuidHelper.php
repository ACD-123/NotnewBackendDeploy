<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GuidHelper extends Arr
{
    public static function getGuid(): string
    {
        return Str::uuid()->toString();
    }
    public static function getShortGuid(): string
    {
        return Str::random(8);
    }
    public static function getnotificationId(): string
    {
        return Str::random(2);
    }
}