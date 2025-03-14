<?php

namespace App\Models;

use App\Core\Base;
use App\Interfaces\IMediaInteraction;
use App\Traits\InteractWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Service
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float $sale_price
 * @property string $location
 * @property string $google_address
 * @property string $postal_address
 * @property float $longitude
 * @property float $latitude
 * @property boolean $active
 * @property string $guid
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property Rating[] $ratings
 * @property ServicesCategories[] $servicesCategories
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereGoogleAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service wherePostalAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service whereUserId($value)
 * @mixin \Eloquent
 */
class Service extends Base implements IMediaInteraction
{
    use InteractWithMedia;

    public const MEDIA_UPLOAD = "SERVICES";
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['category_id', 'user_id', 'name', 'description', 'price', 'status', 'sale_price', 
    'location', 'google_address', 'postal_address', 'longitude', 'latitude', 'active', 'guid', 'created_at',
     'updated_at', 'IsSaved', 'is_sold'];

    protected $appends = ['cover_image', 'is_owner'];

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
    public function ratings()
    {
        return $this->hasMany('App\Models\Rating');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function withCategory()
    {
        return $this->load(['category' => function (BelongsTo $query) {
            $query->with('attributes');
        }]);
    }

    public function servicesAttributes()
    {
        return $this->hasMany(ServicesAttribute::class);
    }

    public function withServicesAttributes()
    {
        return $this->load('servicesAttributes');
    }

    public function getIsOwnerAttribute()
    {
        return Auth::guard('api')->check();
    }

    public function withMedia()
    {
        return $this->load('media');
    }

    public function withUser()
    {

        return $this->load('user');
    }
     /**
     * this is temp fix for the Demo its should  be field in the services table
     * @return |null
     */
    public function getCoverImageAttribute()
    {
        $media = $this->media->first();
        if (!empty($media)) {
            return $media->url;
        }
        return null;
    }

    public function savedUsers()
    {
        return $this->hasMany(SavedUsersService::class, 'service_id');
    }
     /**
     *User that method as in Trait so what so ever we also used in service just passing the same name in relation
     */
    public function attachOrDetachSaved()
    {
        if (Auth::check()) {
            $authenticatedUserId = \Auth::user()->id;

            $savedItem = $this->savedUsers()->where('user_id', $authenticatedUserId)->first();

            if (!empty($savedItem)) {
                return $savedItem->delete();
            }
            $this->savedUsers()->save(new SavedUsersService(["user_id" => $authenticatedUserId]));         
        }
    }

    public function getIsSavedAttribute(): bool
    {
        if (auth('api')->check()) {
            return $this->savedUsers->contains('user_id', auth('api')->user()->id);
        }
        return false;
    }

    public function appendDetailAttribute()
    {
        $this->append(['isSaved']);
        return $this;
    }

}
