<?php

namespace App;

use App\Pay\Model\MasterContainer;
use App\Pay\Model\SettleContainer;
use Illuminate\Database\Eloquent\Model;
use Skip32;

class Transfer extends Model
{
    protected $table = 'transfer';
    protected $keyType = 'string';
    //交易记录
    public function record()
    {
        return $this->hasMany('App\TransferRecord', 'transfer_id', 'id');
    }

    //发起交易人
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    //交易所属店铺
    public function shop()
    {
        return $this->belongsTo('App\Shop', 'shop_id', 'id');
    }

    //参与交易人
    public function joiner()
    {
        return $this->hasMany('App\TransferUserRelation', 'transfer_id', 'id');
    }

    //红包茶水费记录
    public function tips()
    {
        return $this->hasMany('App\TipRecord', 'transfer_id', 'id');
    }

    use Skip32Trait;

    protected static $skip32_id = 'e05dae2bb8c69cb437fe';

    public function en_shop_id()
    {
        return Skip32::encrypt(self::$skip32_id, $this->shop_ip);
    }

    public function container()
    {
        return $this->hasOne(SettleContainer::class, 'id', 'container_id');
    }
}
