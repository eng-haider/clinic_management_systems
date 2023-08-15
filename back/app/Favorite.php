<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Favorite extends Model
{
    protected $fillable = ['user_id'];
    protected $table = 'favorites';

    public function favoriteable(){
        return $this->morphTo('favoriteable');
    }

    


    
    public function user(){
        return $this->belongsTo('App\User');
    }



}
