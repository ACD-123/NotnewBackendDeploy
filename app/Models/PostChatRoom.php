<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostChatRoom extends Model
{
    use HasFactory;

    protected $table = 'post_chat_room';

    protected $fillable = ['user_id','participant_id','post_product_guid','status','extra_field','deleted_by'];

    public function messages()
    {
        return $this->hasMany(PostChatMessage::class,'room_id');
    }
}
