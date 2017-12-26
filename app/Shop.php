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
    use Skip32Trait;

    const STATUS_NORMAL = 0;

    const STATUS_CLOSED = 1;

    const STATUS_FREEZE = 2;

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


    protected static $skip32_id = '0123456789abcdef0123';

    public function container() {
        return $this->hasOne(MasterContainer::class, 'id', 'container_id');
    }

    public function transfer() {
        return $this->hasMany('App\Transfer', 'shop_id', 'id');
    }
}
