<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package App
 * @property integer $id
 * @property string $name
 * @property string $mobile
 * @property string $password
 */
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

    //发起的交易
    public function transfer()
    {
        return $this->hasMany('App\Transfer', 'user_id', 'id');
    }

    //交易记录
    public function transfer_record()
    {
        return $this->hasMany('App\TransferRecord', 'user_id', 'id');
    }

    //茶水费
    public function tips()
    {
        return $this->hasMany('App\TipRecord', 'user_id', 'id');
    }

    //产出利润
    public function output_profit()
    {
        return $this->hasMany('App\Profit', 'user_id', 'id');
    }

    //参与的交易
    public function involved_transfer()
    {
        return $this->hasMany('App\TransferUserRelation', 'user_id', 'id');
    }

    //代理
    public function parent()
    {
        return $this->hasOne('App\User', 'id', 'parent_id');
    }

    //运营
    public function operator()
    {
        return $this->hasOne('App\Admin', 'id', 'operator_id');

    }
    
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
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function in_shops() {
        return $this->belongsToMany(Shop::class, (new ShopUser)->getTable(), 'user_id', 'shop_id');
    }

    public function wechat_user() {
        return $this->hasOne(OauthUser::class, 'user_id');
    }

}
