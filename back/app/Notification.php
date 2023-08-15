<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'id';

    protected $fillable = ['notification_title','notification_body','user_id','notification_type_id','sending_date','status_id'];

    protected $formattedDates = [
        [
         'key'        => 'sending_date',
         'in_format'  => 'Y-m-d H:i',
         'out_format' => 'Y-m-d H:i'
        ],
        'starts_at',
        'ends_at'
    ];
 

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function notification_type()
    {
        return $this->belongsTo('App\NotificationType');
    }



    public function Notificationable(){

        return $this->morphTo();
    }


    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }

    
}
