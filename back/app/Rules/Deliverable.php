<?php

namespace App\Rules;

use App\Item;
use Illuminate\Contracts\Validation\Rule;

class Deliverable implements Rule
{
    protected $val;

    public function __construct()
    {
        //
    }


    public function passes($attribute, $value)
    {
        $this->val = $attribute;
        if(Item::find($value)){
            return Item::find($value)->deliverable === 1;
        }
        return false;


    }


    public function message()
    {
        return 'The validation error message.'.$this->val;
    }
}
