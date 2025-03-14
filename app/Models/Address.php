<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpHelper;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Address
{
    public static function getDatabyApi($zipcode)
    {
        $clientKey = env('ZIPCODEAPI_CLIENTKEY');
        /**
         * New York Zip Code 
         * For Testing
         * $zipcode = "10002" 
         */

        $url = "https://www.zipcodeapi.com/rest/".$clientKey."/info.json/".$zipcode."/radians";
        // $url ="https://maps.googleapis.com/maps/api/geocode/json?address=" . $zipcode . "&key=AIzaSyBHMg5B4QucbVGKUb6_Qj9R76OnHflXqc4&sensor=true";
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        
        return HttpHelper::request($url, null, 'POST', $headers, true);
       
    }
}
