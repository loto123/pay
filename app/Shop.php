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


    public function en_id() {
        return Skip32::encrypt("0123456789abcdef0123", $this->id);
    }

    public static function findByEnId($en_id) {
        return self::find(Skip32::decrypt("0123456789abcdef0123", $en_id));
    }

    public function container() {
        return $this->hasOne(MasterContainer::class, 'container_id');
    }
}
