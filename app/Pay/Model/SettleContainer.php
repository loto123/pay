<?php
/**
 * 结算容器
 * 用于向多个主容器收发资金
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:51
 */

namespace App\Pay\Model;


class SettleContainer extends Container
{
    protected $table = 'pay_settle_container';
}