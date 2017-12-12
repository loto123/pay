<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    //
    public function user()
    {
        return $this->belongsTo('App\user','manager','id');
    }

    public function shop_user(){
        return $this->hasMany('App\ShopUser','shop_id','id');
    }


}
