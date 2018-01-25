<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PetRecord
 * @property integer $pet_id
 * @property integer $from_user_id
 * @property integer $to_user_id
 * @property integer $type
 * @package App
 */
class PetRecord extends Model
{
    //
    const TYPE_NEW = 0;//系统初始

    const TYPE_TRANSFER = 1;//交易转移

    const TYPE_CANCEL = 2;//订单取消补偿

    public function from_user() {
        return $this->belongsTo(User::class)->withDefault(['name' => '系统']);
    }

    public function to_user() {
        return $this->belongsTo(User::class);
    }

    public function pet() {
        return $this->belongsTo(Pet::class);
    }
}
