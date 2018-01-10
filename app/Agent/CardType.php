<?php

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;

class CardType extends Model
{
    protected $table = 'agent_card_type';
    protected $guarded = ['id'];

    /**
     * 取得该类型所有卡
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cards()
    {
        return $this->hasMany(Card::class, 'card_type');
    }
}
