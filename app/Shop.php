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
    public function manager()
    {
        return $this->hasOne('App\User','manager');
    }

    public function shop_user(){
        return $this->hasMany('App\ShopUser','shop_id','id');
    }

    public function users() {
        return $this->belongsToMany(User::class, (new ShopUser)->getTable(), 'shop_id', 'user_id');
    }


}
