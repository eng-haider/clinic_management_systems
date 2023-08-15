<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $table = 'type';

    protected $primaryKey = 'payment_type_id';

    protected $fillable = [
        'payment_type_name'
    ];

    public function payment()
    {
        return $this->hasMany('App\Payment');
    }


}
