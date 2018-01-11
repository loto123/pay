<?php
/**
 * id混淆器,仅适用于无符号id
 * Author: huangkaixuan
 * Date: 2017/12/19
 * Time: 16:10
 */

namespace App\Pay;


use Mockery\Exception;

class IdConfuse
{
    const RANDOM_SEED = 7;


    /**
     * 混淆id
     * @param $id int 需要混淆的id
     * @pram $padTo int 填充到指定长度,默认不填充
     * @param $number_only boolean 是否仅允许数字
     * @return string 混淆后的字符串
     */
    public static function mixUpId($id, $padTo = 0, $number_only = false)
    {
        if (self::RANDOM_SEED > 9 || self::RANDOM_SEED < 0) {
            throw new Exception('Invalid seed' . self::RANDOM_SEED);
        }

        //填充位数
        $padded = sprintf("%0{$padTo}d", $id);

        //逐位取补
        $len = strlen($padded);
        for ($i = 0; $i < $len; $i++) {
            $tmp = 9 - $padded[$i] + self::RANDOM_SEED + $i % 9;
            $padded[$i] = $number_only ? $tmp % 10 : self::num2alpha($tmp);
        }
        return $padded;
    }

    private static function num2alpha($num)
    {
        return $num < 10 ? $num : chr(97 + $num);
    }

    /**
     * 恢复id
     * @param $padded
     * @param bool $number_only
     * @return int 从混淆的字符串中恢复id
     */
    public static function recoveryId($padded, $number_only = false)
    {
        if (self::RANDOM_SEED > 9 || self::RANDOM_SEED < 0) {
            throw new Exception('Invalid seed' . self::RANDOM_SEED);
        }

        //逐位取补
        $len = strlen($padded);
        for ($i = 0; $i < $len; $i++) {
            $tmp = $number_only ? $padded[$i] : self::alpha2num($padded[$i]);
            $tmp = 9 + self::RANDOM_SEED + $i % 9 - $tmp;
            $padded[$i] = $tmp >= 0 ? $tmp % 10 : 10 + $tmp % 10;
        }

        return (int)$padded;
    }

    private static function alpha2num($num)
    {
        return is_numeric($num) ? $num : ord($num) - 97;
    }
}