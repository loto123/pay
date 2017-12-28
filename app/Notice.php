<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{

    protected $table = 'notifications';

    public static function typeConfig()
    {
        return [
            1=>'App\Notifications\ProfitApply',
            2=>'App\Notifications\UserApply',
            3=>'App\Notifications\SystemApply',
        ];
    }
}
