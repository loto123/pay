<?php

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;

class CardTransfer extends Model
{
    protected $table = 'agent_card_transfer';
    protected $guarded = ['id'];
}
