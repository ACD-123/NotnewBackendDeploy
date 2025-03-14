<?php

namespace App\Models;

use App\Core\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property integer $id
 * @property integer $product_id
 * @property integer $requester_id
 * @property integer $user_id
 * @property float $price
 * @property integer $status_id
 * @property integer $status_name
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property requester $requester
 * @property Product $product
 * @property Order[] $orders
 */
class Offer extends Base
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
    protected $fillable = ['product_id', 'requester_id', 'user_id', 'price', 'status_id', 'status_name', 'created_at', 'updated_at', 'guid'];

    protected $casts = [
        'created_at' => 'date:M d, Y g:i A',
       ];
    public static $STATUS_NEW_REQUEST = 'NEW_REQUEST';
    public static $STATUS_REJECT = 'Rejected';
    public static $STATUS_ACCEPT = 'Accepted';
    public static $STATUS_SOLD = 'SOLD';


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */


    public function requester()
    {
        return $this->belongsTo('App\Models\User', 'requester_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order','offer_id','id');
    }

    public function isSold_Products() {

        return $this->product()->where('is_sold','=', false);
    
    }

    public static function defaultSelect()
    {
        return ['id'];
    }
    public static function request(Product $product, $offer)
    {
        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'requester_id' => Auth::user()->id
            ],
            [
                'user_id' => $product->user_id,
                'price' => $offer,
                'status_name' => self::$STATUS_NEW_REQUEST
            ]
        );
    }

    public static function cancelOffer($offer)
    {
        Offer::where('id',$offer)
            ->where('requester_id', Auth::user()->id)
            ->delete();
        
    }

}
