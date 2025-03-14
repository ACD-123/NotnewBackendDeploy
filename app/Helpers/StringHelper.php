<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StringHelper extends Str
{
    public static function getCharacters($string, $number = 50, $from = 0)
    {
        return substr($string, 0, 100);
    }

    public static function guid_slug($name, $guid)
    {
        $title = preg_replace("![^a-z0-9]!i", "-", self::getCharacters($name));
        return "{$title}/$guid";
    }

    public static function isEmail($value): bool
    {
        return static::contains($value, '@');
    }

    public static function isBoolean($value): bool
    {
        return static::isValueTrue($value) || static::isValueFalse($value);
    }

    public static function isValueTrue($value): bool
    {
        return ArrayHelper::inArray(self::trimLower($value), ['true', '1', 'yes'])
            || $value === 1
            || $value === true;
    }

    public static function isValueFalse($value): bool
    {
        return empty($value) || ArrayHelper::inArray(self::trimLower($value), ['false', '0', 'no'])
            || $value === 0
            || $value === false;
    }

    public static function trimLower(?string $str): string
    {
        return static::lower(trim($str));
    }

    public static function compareInt($string, $string1)
    {
        return intval($string) === intval($string1);
    }

    public static function isInt(string $string):int
    {
        return is_int($string);
    }

    public static function createKey()
    {
        $str=rand();
        return md5($str);
    }
}