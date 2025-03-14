<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

class ArrayHelper extends Arr
{
    public static function inArray($needle, array $haystack, bool $strict = false): bool
    {
        return in_array($needle, $haystack, $strict);
    }

    public static function search($needle, array $haystack, $strict = null)
    {
        return array_search($needle, $haystack, $strict);
    }

    public static function notInArray($needle, array $haystack, bool $strict = false): bool
    {
        return !self::inArray($needle, $haystack, $strict);
    }

    public static function split($array, $preserveKeys = true, ...$parts): array
    {
        $start = 0;
        $splitArrays = [];
        foreach ($parts as $index => $part) {
            $splitArrays[$index] = array_slice($array, $start, $part, $preserveKeys);
            $start += $part;
        }
        $splitArrays[count($parts)] = array_slice($array, $start, null, $preserveKeys);

        return $splitArrays;
    }

    public static function isEmpty($array): bool
    {
        return empty($array);
    }

    /**
     * Inverse of static::isEmpty
     * @param $array
     * @return bool
     */
    public static function isNotEmpty($array): bool
    {
        return !static::isEmpty($array);
    }

    public static function trimLower(array $array)
    {
        return array_map(function ($item) {
            return strtolower(trim($item));
        }, $array);
    }

    public static function trimFilter(array $array)
    {
        return array_filter(array_map(function ($item) {
            return is_string($item) ? trim($item) : $item;
        }, $array));
    }

    public static function trimLowerFilter(array $array)
    {
        return static::filter(static::trimLower($array));
    }

    public static function filter(array $array)
    {
        return array_filter($array);
    }

    public static function isArray($array): bool
    {
        return is_array($array);
    }

    public static function pluckFirst(array $array)
    {
        if (static::isNotEmpty($array)) {
            return $array[0];
        }

        return null;
    }

    /**
     * @param array $array
     * @return int
     * @throws \Exception
     */
    public static function getIndexOfLastElement(array $array): int
    {
        if (static::isNotEmpty($array)) {
            return count($array) - 1;
        }

        throw new \Exception('Cannot calculate last index of an empty array');
    }

    /**
     * @param array $array
     * @return mixed
     * @throws \Exception
     */
    public static function getLastElement(array $array)
    {
        if (static::isNotEmpty($array)) {
            return $array[static::getIndexOfLastElement($array)];
        }

        return null;
    }

    //@TODO: This function does not work if $searchValue is not provided. ISSUE is in giving default values of searchValue and $strict. php null is not of same type as used in array_keys.
    public static function keys(array $input, $searchValue = null, $strict = null): array
    {
        return array_keys($input, $searchValue, $strict);
    }

    public static function column(array $array, $column, $index_key = null): array
    {
        return array_column($array, $column, $index_key);
    }

    public static function flip(array $array): array
    {
        return array_flip($array);
    }

    public static function intersect(array $array1, array $array2): array
    {
        return array_intersect($array1, $array2);
    }
   
    /*
     * You can change value of last glue.
     * */
    public static function advancedImplode($array, $glue = '', $lastGlue = ''): string
    {
        $last = array_slice($array, -1);
        $first = implode($glue, array_slice($array, 0, -1));
        $both = array_filter(array_merge([$first], $last), 'strlen');

        return implode($lastGlue, $both);
    }

    public static function removeKeys(array $array, array $keys): array
    {
        if (empty($keys)) {
            return $array;
        }

        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    public static function removeByValue(array $array, array $values, bool $resetKeys = true): array
    {
        foreach ($values as $item) {

            if (($key = array_search($item, $array, true)) !== false) {
                unset($array[$key]);
            }
        }

        if ($resetKeys) {
            return array_values($array);
        }

        return $array;
    }

    public static function merge(array $array1, array ...$array2)
    {
        return array_merge($array1, ...$array2);
    }

    public static function mapStringToInt(array $array)
    {
        return array_map('intval', $array);
    }

    public static function keyExists($key, array $search)
    {
        return array_key_exists($key, $search);
    }

    public static function combine(array $keys, array $values)
    {
        return array_combine($keys, $values);
    }
    public static function otpGenerate()
    {
        return mt_rand(1000,9999);
    }
    /*
     * This array_column_ext is different from standard as it does not return null,
     * when column value is empty string.
     * */
    public static function extractColumn($array, $column): array
    {
        $result = [];

        foreach ($array as $value) {

            if (is_array($value) && array_key_exists($column, $value) && !empty($value[$column])) {
                $result[] = $value[$column];
            }
        }

        return $result;
    }

    /**
     * return the bool if both array same or different
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    public static function arrayCompare(array $array1, array $array2): bool
    {
        return !empty(array_diff($array1, $array2));
    }

    /**
     * Found the number of value repeat in array and return count
     *
     * @param $needle
     * @param array $array
     * @return int
     */
    public static function arraySearchCount($needle, array $array): int
    {
        $counts = array_count_values($array);
        return $counts[$needle];
    }

    public static function sortByOrderArray($a, $b, $orderArray): int
    {
        $o1 = $orderArray[$a];
        $o2 = $orderArray[$b];

        return $o1 - $o2;
    }

    public static function getUniqueFromArray($array1, $array2)
    {
        return self::merge(array_diff($array1, $array2), array_diff($array2, $array1));
    }
}