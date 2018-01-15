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
    const CARD_NO_LENGTH = 8;
    const UPDATED_AT = null;
    const UNBOUND = 0;
    const BOUND = 1;
    const FROZEN = 1;
    const UNFROZEN = 0;
    protected $table = 'agent_card';
    protected $guarded = ['id'];

    /**
     * 从卡号取得卡id
     * @param $mixed
     * @return int
     */
    public static function recover_id($mixed)
    {
        return IdConfuse::recoveryId($mixed, true);
    }

    /**
     * 取得卡号
     * @return string 8位卡号
     */
    public function mix_id()
    {
        return IdConfuse::mixUpId($this->id, self::CARD_NO_LENGTH, true);
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
    public function owner_user()
    {
        return $this->belongsTo(User::class, 'owner');
    }


    //库存
    public function stock()
    {
        return $this->hasOne('App\Agent\CardStock', 'card_id', 'id');
    }

    //使用记录
    public function card_use()
    {
        return $this->hasMany('App\Agent\CardUse', 'card_id', 'id');
    }

    //推广员
    public function promoter()
    {
        return $this->belongsTo('App\User', 'promoter_id', 'id');
    }

}
