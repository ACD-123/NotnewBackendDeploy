<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PostChatMessage extends Model
{
    use HasFactory;

    protected $table = 'post_chat_message';

    protected $appends = ['date', 'time'];

    protected $fillable = ['room_id', 'user_id', 'from_id', 'message', 'timestamp', 'is_read', 'is_seen','type','link', 'deleted_by'];

    public function getDateAttribute()
    {
        return Carbon::parse($this->created_at)->format("Y-m-d");
    }
    public function getTimeAttribute()
    {
        return Carbon::parse($this->created_at)->format("H:i A");
    }

    public function chatRoom()
    {
        return $this->belongsTo(PostChatRoom::class,'id','room_id');
    }
}
