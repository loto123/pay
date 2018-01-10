<?php

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;

class CardType extends Model
{
    protected $table = 'agent_card_type';
    protected $guarded = ['id'];
}
