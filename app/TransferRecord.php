<?php

namespace App;

use App\Pay\Model\Transfer;
use Illuminate\Database\Eloquent\Model;

class TransferRecord extends Model
{
    protected $table = 'transfer_record';

    //记录所属交易
    public function transfer()
    {
        return $this->belongsTo('App\Transfer', 'transfer_id', 'id');
    }

    //用户
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    //茶水费
    public function tip()
    {
        return $this->hasOne('App\TipRecord', 'record_id', 'id');
    }

    //容器事务
    public function pay_transfer()
    {
        return $this->hasOne(Transfer::class, 'pay_transfer_id', 'id');
    }

//    public function en_id() {
//        return Skip32::encrypt("0123456789abcdef0123", $this->id);
//    }
//
//    public static function findByEnId($en_id) {
//        return self::find(Skip32::decrypt("0123456789abcdef0123", $en_id));
//    }
}
