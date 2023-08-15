<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','payment_type_id',
        'card_user_name',
        'card_number','expiry_date',
        'payment_token',
        'status_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function payment_type()
    {
        return $this->belongsTo('App\PaymentType');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }
}
