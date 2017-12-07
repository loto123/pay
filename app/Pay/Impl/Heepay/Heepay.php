<?php
/**
 * 汇付宝
 * Author: huangkaixuan
 * Date: 2017/12/4
 * Time: 16:20
 */

namespace App\Pay\Impl\Heepay;

use App\Pay\PlatformInterface;
use Illuminate\Http\Request;

class Heepay implements PlatformInterface
{
    public function acceptNotify(Request $request, array $config)
    {
        // TODO: Implement acceptNotify() method.
    }
}