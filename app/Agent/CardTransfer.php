<?php

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * 卡转让
 * Class CardTransfer
 * @package App\Agent
 */
class CardTransfer extends Model
{
    const UPDATED_AT = false;
    protected $table = 'agent_card_transfer';
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
     * 接收人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function to()
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
