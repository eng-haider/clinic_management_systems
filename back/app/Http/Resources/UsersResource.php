<?php

namespace App\Http\Resources;
use App\Models\Permissions;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return
        ['id'=>$this->id,
        'name'=>$this->name,
        'phone'=>$this->phone,
        'email'=>$this->email,
         'doctor'=>$this->Doctor,
         'role_id'=>$this->role_id,
         'Clinics'=>$this->role_id==2 ||$this->role_id==5?$this->Doctor->Clinics:$this->secretary->Clinics,
         'img_file'=>$this->img_name,
         'Permissions'=>Permissions::get(),
         'role'=>$this->Role,


    ];
    }
}
