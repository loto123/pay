<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Shop
 * @package App
 * @property integer $id
 * @property string $name
 * @property string $logo
 */
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
