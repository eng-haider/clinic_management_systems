<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TasdidBillsReceived extends Model
{
    protected $guarded = [];

    protected $table = 'tasdid_bills_received';

    protected $casts = [
        'CreateDate' => 'datetime:yyyy-mm-ddThh:mm:ss',
        'DueDate' => 'datetime:yyyy-mm-ddThh:mm:ss',
        'PayDate' => 'datetime:yyyy-mm-ddThh:mm:ss',
    ];
}
