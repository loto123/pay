<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobile', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 我管理的店铺
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shop()
    {
        return $this->hasMany('App\Shop','manager','id');
    }

    /**
     * 我参与的店铺
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function in_shops() {
        return $this->belongsToMany(Shop::class, (new ShopUser)->getTable(), 'user_id', 'shop_id');
    }

}
