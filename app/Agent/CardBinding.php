<?php

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * VIP卡绑定
 * Class CardBinding
 * @package App\Agent
 */
class CardBinding extends Model
{
    protected $table = 'agent_card_binding';
    protected $guarded = ['id'];

    /**
     * 绑定代理
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * 绑定卡
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * 取得推广员
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_id');
    }
}
