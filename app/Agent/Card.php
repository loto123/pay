<?php

namespace App\Agent;

use App\Pay\IdConfuse;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * 代理VIP卡
 * Class Card
 * @package App\Agent
 */
class Card extends Model
{
    const UPDATED_AT = false;
    const UNBOUND = 0;
    const BOUND = 1;
    protected $table = 'agent_card';
    protected $guarded = ['id'];

    /**
     * 取得卡号
     * @return string 8位卡号
     */
    public function getIdAttribute($id)
    {
        return IdConfuse::mixUpId($id, 8, true);
    }

    public function setIdAttribute($value)
    {
        $this->attributes['id'] = IdConfuse::recoveryId($value, true);
    }

    /**
     * 取得卡类型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(CardType::class, 'card_type');
    }

    /**
     * 持有人
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner');
    }
}
