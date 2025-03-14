<?php

namespace App\Helpers;

class HttpHelper
{
    public static function request($url, $body, $method = 'GET', $headers = [], $postJson = false)
    {
        $payload = [];

        if($postJson) {
            $payload = json_encode($body);
        } else {
            $payload = http_build_query($body); 
        }

        $ch = curl_init($url);
 
        if ($method === 'GET') {
            // @TODO: implement as required
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // returning special character without this
       
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public static function requestXML($url, $body, $headers = [], $postXML = true)
    {
        
        $ch = curl_init($url);

        if($postXML) {
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "XML=".$body);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);
        // return htmlentities(strval($response));
        $xml = simplexml_load_string($response);
        $json = json_encode($xml, JSON_PRETTY_PRINT);
        $array = json_decode($json, true);
        return $array;
        // return simplexml_load_string($response);
    }

}
