<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Skip32;

class Transfer extends Model
{
    protected $table = 'transfer';

    //交易记录
    public function record() {
        return $this->hasMany('App\TransferRecord', 'transfer_id', 'id');
    }

    //发起交易人
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    //交易所属店铺
    public function shop() {
        return $this->belongsTo('App\Shop','shop_id', 'id');
    }

    //参与交易人
    public function joiner() {
        return $this->hasMany('App\TransferUserRelation', 'transfer_id', 'id');
    }

    //红包茶水费记录
    public function tips() {
        return $this->hasMany('App\TipRecord', 'transfer_id', 'id');
    }

    public function en_id() {
        return Skip32::encrypt("0123456789abcdef0123", $this->id);
    }

    public function en_shop_id() {
        return Skip32::encrypt("0123456789abcdef0123", $this->shop_ip);
    }

    public static function findByEnId($en_id) {
        return self::find(Skip32::decrypt("0123456789abcdef0123", $en_id));
    }
}
