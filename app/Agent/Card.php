<?php

namespace App\Agent;

use App\Pay\IdConfuse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 代理VIP卡
 * Class Card
 * @package App\Agent
 */
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

    public function setIdAttribute($value)
    {
        $this->attributes['id'] = IdConfuse::recoveryDepositId($value, true);
    }

    /**
     * 取得配卡人
     */
    public function allocateBy()
    {
        return DB::table('admin_users')->find($this->allocator_id);
    }

    /**
     * 取得运营
     */
    public function allocateTo()
    {
        return DB::table('admin_users')->find($this->allocator_to);
    }

    /**
     * 取得卡类型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(CardType::class, 'card_type');
    }
}
