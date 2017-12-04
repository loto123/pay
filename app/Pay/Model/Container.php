<?php

namespace App\Pay\Model;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $table = 'pay_container';


    /**
     * 冻结资金
     *
     * @param $amount float
     */
    public function freeze($amount)
    {
        //TODO
    }


    /**
     * 解冻资金
     *
     * @param $amount float
     */
    public function unfreeze($amount)
    {
        //TODO
    }
}
