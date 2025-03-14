<?php

namespace App\Models\Setting;

use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    protected $fillable = ['name', 'category', 'value', 'active', 'guid'];

    protected $appends = ['parseValue'];

    public function getParseValueAttribute()
    {
        if (StringHelper::isBoolean($this->value)) {
            return StringHelper::isValueTrue($this->value);
        };

        return $this->value;
    }

    public static function getValue($value)
    {
        return Setting::where('name', $value)->first()->parseValue;
    }

    public static function isServiceActive()
    {
        return Setting::getValue('providerShouldActive');
    }

    public static function requiredOtp(){
        return Setting::getValue('requiredOtp');
    }
}
