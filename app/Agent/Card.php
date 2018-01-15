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
    const FROZEN = 1;
    const UNFROZEN = 0;
    protected $table = 'agent_card';
    protected $guarded = ['id'];

    /**
     * 取得卡号
     * @return string 8位卡号
     */
    public function mix_id()
    {
        return IdConfuse::mixUpId($this->id, 8, true);
    }

    public function recover_id()
    {
        $this->attributes['id'] = IdConfuse::recoveryId($this->id, true);
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

    //运营
    public function operators()
    {
        return $this->belongsToMany('App\Admin',(new CardStock())->getTable(),'operator','id');
    }

    //推广员
    public function promoters()
    {
        return $this->belongsToMany('App\user',(new CardUse())->getTable(),'card_id','from');
    }
}
