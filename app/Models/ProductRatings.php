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

class ProductRatings extends Model
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
    protected $fillable = ['product_id', 'user_id', 'order_id'];

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }

    public function product()
    {
        return $this->hasOne('App\Models\Product');
    }
}
