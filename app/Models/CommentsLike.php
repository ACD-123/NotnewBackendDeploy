<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CommentsLike
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $comment_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Comment $comment
 * @property User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CommentsLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentsLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentsLike query()
 * @mixin \Eloquent
 */
class CommentsLike extends Model
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
    protected $fillable = ['user_id', 'comment_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo('App\Models\Comment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
