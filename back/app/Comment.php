<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'Comments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'item_id',
        'comment_content'
    ];

    public function item()
    {
        return $this->hasMany('App\Item');
    }
    public function user()
    {
        return $this->hasMany('App\User');
    }
}
