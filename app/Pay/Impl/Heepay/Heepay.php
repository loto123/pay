<?php
/**
 * 汇付宝
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:20
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\Model\Platform;
use App\Pay\PlatformInterface;

class Heepay extends Platform implements PlatformInterface
{

    public function withdraw($amount, $receiver_info)
    {
        // TODO: Implement withdraw() method.
    }

    public function acceptNotify()
    {
        // TODO: Implement acceptNotify() method.
    }

    public function gatherConfigure(array $args)
    {
        // TODO: Implement gatherConfigure() method.
    }
}