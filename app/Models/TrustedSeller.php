<?php

namespace App\Models;

use App\Notifications\RegistrationVerificationNotification;
use App\Observers\UserObserver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use App\Helpers\StripeHelper;

class TrustedSeller extends Model
{
    protected $fillable = ['name', 'email', 'user_id', 'address', 
                    'number', 'store', 'facebook', 'instagram', 'ein', 
                    'ssn', 'businessType', 'website', 'shipmenttype', 
                    'price', 'days', 'percentage', 'courriertype'];

    public function trustedSellers()
    {
        return $this->hasOne('App\Models\User');
    }
}
