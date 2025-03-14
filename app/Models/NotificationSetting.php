<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    public $table = "notification_setting";
   /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['order_updates', 'item_ending', 'item_updates', 'auction_updates', 'offer_updates', 'presonalized_recomendations', 
            'rewards_offers', 'general_promotions', 'selling_presonalized_recomendations', 'selling_rewards_offers',
            'selling_general_promotions', 'user_id', 'created_at', 'updated_at'];

    public static function defaultSelect()
    {
        return ['order_updates', 'item_ending', 'item_updates', 'auction_updates', 'offer_updates', 'presonalized_recomendations', 
        'rewards_offers', 'general_promotions', 'selling_presonalized_recomendations', 'selling_rewards_offers',
        'selling_general_promotions', 'user_id', 'created_at', 'updated_at'];
    }   

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
