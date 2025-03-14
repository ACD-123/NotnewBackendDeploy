<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveSearch extends Model
{
    public $table = "tbl_save_search";
    
     /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['keywords', 'email_alert','guid','user_id', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
