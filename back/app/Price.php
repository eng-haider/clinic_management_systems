<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'prices';

    protected $primaryKey = 'id';

    protected $fillable = ['price_value','payment_when_receiving',
    'free_cancellation','deduction','payment_after_awhile',
    'cancellation_deduction_ratio','installments'];

    protected $hidden = [
        'item_id','updated_at', 'created_at',
    ];

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

}
