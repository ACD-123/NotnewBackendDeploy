<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    use HasFactory;

    protected $table = 'chat_message';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['room_id','uid','from_id','message','timestamp','message_type','status','extra_field','is_read','is_seen'];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
