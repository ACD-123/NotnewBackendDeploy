<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ShippingDetail extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    //protected $fillable = ['user_id', 'name', 'street_address', 'state', 'city', 'zip', 'created_at', 'updated_at'];
        protected $fillable = ['user_id', 'name', 'latitude', 'longitude', 'country' ,'street_address', 'state', 'city', 'zip', 'created_at', 'updated_at'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }
    
    public static function defaultSelect()
    {
        return [ 'id','user_id', 'name', 'street_address', 'state', 'city', 'zip', 'created_at', 'updated_at'];
    }
    
    public static function shippingCurl($endpoint, $method = 'GET', $data = [])
    {
    $apiKey = 'TEST_l7EknHfOt7KnT9PBVk4qxr8ZrOQxMNmGcO6D6gDgk2s';
    $url = 'https://api.shipengine.com/v1/' . $endpoint;

    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'API-Key: ' . $apiKey
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return ['error' => $error];
    } else {
        return json_decode($response, true);
    }
}


}
