<?php
/**
 * Created by PhpStorm.
 * User: nielixin
 * Date: 2018/3/28
 * Time: 10:35
 */

namespace App;


class CustomMath
{
    const SCALE = 3;

    public static function result(string $result)
    {
        return bcdiv(ceil(bcmul($result, 100, 1)), 100 ,2);
    }

}