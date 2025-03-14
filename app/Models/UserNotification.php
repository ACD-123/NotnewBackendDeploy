<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property integer $notifiable_id
 * @property string $data
 * @property string $read_at
 * @property string $created_at
 * @property string $updated_at
 */
class UserNotification extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $table = 'tbl_user_notifications';
    protected $keyType = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at'];

    public static function defaultSelect()
    {
        return ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at'];
    }   
}
