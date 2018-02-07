<?php

namespace App;

use App\Pay\Model\MasterContainer;
use Illuminate\Database\Eloquent\Model;
use Skip32;

/**
 * Class Shop
 * @package App
 * @property integer $id
 * @property string $name
 * @property string $logo
 * @property boolean $use_link
 * @property boolean $active
 * @property double $price
 * @property double $fee
 * @property integer $container_id
 * @property MasterContainer $container
 */
class Shop extends Model
{
    protected $keyType = 'string';

    use Skip32Trait;

    const STATUS_NORMAL = 0;

    const STATUS_CLOSED = 1;

    const STATUS_FREEZE = 2;

    public function getLogoAttribute($value)
    {
        return $value ? url($value) : asset("images/personal.jpg");
    }

    //店铺铺主
    public function manager()
    {
        return $this->hasOne('App\User', 'id', 'manager_id');
    }

    public function shop_user()
    {
        return $this->hasMany('App\ShopUser', 'shop_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, (new ShopUser)->getTable(), 'shop_id', 'user_id');
    }


    protected static $skip32_id = 'ecf724065cb8387f6b82';

    public function container()
    {
        return $this->hasOne(MasterContainer::class, 'id', 'container_id');
    }

    public function transfer()
    {
        return $this->hasMany('App\Transfer', 'shop_id', 'id');
    }

    public function tips()
    {
        return $this->hasMany(TipRecord::class, 'shop_id', 'id');
    }

    public function funds()
    {
        return $this->hasMany(ShopFund::class, 'shop_id');
    }

    public function totalProfit($condition = [])
    {
        //打赏金额
//        $reward = $this->tips()->where($condition)->where('record_id', 0)->sum('amount');
        //交易产生店铺手续费
//        $fee = $this->tips()->where($condition)->where('record', '>', 0)->whereHas('transfer', function ($query) {
//            $query->where('status', 3);
//        })->sum('amount');
//        return bcadd($reward, $fee);
        return $this->tips()->where($condition)->where('status', TipRecord::USEABLE_STATUS)->sum('amount');
    }
}
