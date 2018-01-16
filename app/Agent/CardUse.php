<?php

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * 卡使用
 * Class CardTransfer
 * @package App\Agent
 */
class CardUse extends Model
{
    const TYPE_TRANSFER = 0; //卡转让
    const TYPE_BINDING = 1; //卡绑定

    const UPDATED_AT = null;
    protected $table = 'agent_card_use';
    protected $guarded = ['id'];

    /**
     * 发出人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function from()
    {
        $this->belongsTo(User::class, 'from');
    }

    /**
     * 接受人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to');
    }

    /**
     * 卡
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
