<?php

namespace App\Agent;

use App\Pay\IdConfuse;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'agent_card';
    protected $guarded = ['id'];

    /**
     * 取得卡号
     * @return string 8位卡号
     */
    public function getIdAttribute($id)
    {
        return IdConfuse::mixUpDepositId($id, 8, true);
    }
}
