<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayInterfaceRecord extends Model
{
    //订单处理状态
    const UN_DEAL = 0;
    const DEAL_SUCCESS = 1;
    const UN_RESPONSE = 2;
    const DEAL_FAIL = 3;
}
