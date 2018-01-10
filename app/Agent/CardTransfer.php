<?php

namespace App\Agent;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        return $this->from_promoter ? $this->belongsTo(User::class, 'sender_id') : DB::table('admin_users')->find($this->sender_id);
    }

    /**
     * 接收人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
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
