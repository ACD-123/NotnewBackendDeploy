<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationModel extends Model
{
    use HasFactory;
    
    protected $table='user_notification_models';

    protected $fillable=['user_id','sender_id','title','message','type','is_seen','is_read','notification_type','recieved_from','product_guid','auction_status','room_id','win','url','guid','notificationtype','image'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id','id');
    }
}
