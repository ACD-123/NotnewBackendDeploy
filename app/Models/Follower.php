<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $fillable=['follow_by','follow_to','is_follow','date'];

    public function follow()
    {
        return $this->belongsTo(User::class,'follow_by');   
    }

    public function followed()
    {
        return $this->belongsTo(User::class,'follow_to');   
    }

    public static function getFollowers($userId)
    {
        return Follower::with(['follow', 'follow.media'])->where('follow_to',$userId)->where('is_follow',1)->get();
    }

    public static function getFollowersCount($userId)
    {
        return Follower::where('follow_to',$userId)->where('is_follow',1)->count();
    }
    public static function isFollowing($userId,$followerId)
    {
        $fol=Follower::where('follow_to',$userId)->where('follow_by',$followerId)->where('is_follow',1)->first();
        return $fol ? true : false;
    }
}
