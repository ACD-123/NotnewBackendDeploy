<?php

namespace App\Models;

use App\Core\Base;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @property integer $id
 * @property integer $sender_id
 * @property integer $recipient_id
 * @property integer $product_id
 * @property integer $chat_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $read_at
 * @property string $guid
 * @property string $data
 * @property integer $notifiable_id
 * @property string $notifiable_type
 * @property string $created_at
 * @property string $updated_at
 */
class Message extends Base
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
    protected $fillable = ['sender_id', 'recipient_id', 'product_id', 'chat_id', 'created_by', 'updated_by', 'read_at', 'guid', 'data', 'created_at', 'updated_at', 'notifiable_type', 'notifiable_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }


    /**
     * @throws \Exception
     */
    /*public static function boot()
    {
        parent::boot();
        throw new \Exception("Implementation of the Notification Observer by which message associate with the notification");
    }*/
    /**
     * Move this to  Base model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
//    public function user()
//    {
//        return $this->belongsTo('App\User', 'created_by');
//    }
//
//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
//     */
//    public function user()
//    {
//        return $this->belongsTo('App\User', 'updated_by');
//    }

    public static function defaultSelect(): array
    {
        return ['id', 'recipient_id', 'sender_id', 'data', 'guid'];
    }

    public static function getCount(): int
    {
        return self::select(['id'])
            ->where("recipient_id", Auth::user()->id)
            ->whereNull("read_at")
            ->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public static function getNotifications()
    {
        return self::select(['id', 'data', 'sender_id', 'product_id'])
            ->with(['sender' => function (BelongsTo $belongsTo) {
                $belongsTo->select(['id', 'name']);
            }])
            ->where("recipient_id", Auth::user()->id)
            ->whereNull("read_at")
            ->paginate();
    }
}
