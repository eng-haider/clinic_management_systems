<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

 use Illuminate\Http\Exceptions\HttpResponseException;


class AdvertisingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */


    public function attributes()
    {
        return [
            'item_id' => 'email address',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(

            [
                'success' => false,
                'Data' =>  $validator->errors()
            ]
            
        ));
    }


   

    public function rules()
    {
        return [
            'item_id' => 'numeric',
            'owner_id' => 'numeric',
            'advertising_type_id' => 'required|numeric',
            'adv_description' => 'required',
            'images' => 'required|array|min:1|max:8',
        ];
    }
}
