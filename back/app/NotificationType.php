<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    protected $table = 'notification_type';

    protected $primaryKey = 'id';

    protected $fillable = ['notification_type_name'];

    public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

}
