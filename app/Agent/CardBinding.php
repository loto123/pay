<?php

namespace App\Agent;

use Illuminate\Database\Eloquent\Model;

class CardBinding extends Model
{
    protected $table = 'agent_card_binding';
    protected $guarded = ['id'];
}
