<?php
/**
 * id混淆器,仅适用于无符号id
 * Author: huangkaixuan
 * Date: 2017/12/19
 * Time: 16:10
 */

namespace App\Pay;

class IdConfuse
{
    //数字映射
    private static $numericMap = [
        0 => 3,
        1 => 0,
        2 => 5,
        3 => 4,
        4 => 1,
        5 => 2,
        6 => 7,
        7 => 9,
        8 => 8,
        9 => 6,
    ];

    //混合映射
    private static $numAlphaMap = [
        'odd' => [
            0 => "f",
            1 => "j",
            2 => "c",
            3 => "b",
            4 => "h",
            5 => "d",
            6 => "g",
            7 => "i",
            8 => "e",
            9 => "a",
        ],
        'even' => [
            0 => "9",
            1 => "4",
            2 => "3",
            3 => "1",
            4 => "5",
            5 => "7",
            6 => "6",
            7 => "2",
            8 => "0",
            9 => "8",
        ],

    ];


    /**
     * 混淆id
     * @param $id int 需要混淆的id
     * @pram $padTo int 填充到指定长度,默认不填充
     * @param $number_only boolean 是否仅允许数字
     * @return string 混淆后的字符串
     */
    public static function mixUpId($id, $padTo = 0, $number_only = false)
    {
        //填充位数
        $padded = sprintf("%0{$padTo}d", $id);

        //逐位混淆
        $len = strlen($padded);
        for ($i = 0; $i < $len; $i++) {
            $tmp = ($padded[$i] + $i) % 10;
            $padded[$i] = $number_only ? self::$numericMap[$tmp] : self::$numAlphaMap[$i % 2 == 0 ? 'even' : 'odd'][$tmp];
        }
        return $padded;
    }


    /**
     * 恢复id
     * @param $padded
     * @param bool $number_only
     * @return int 从混淆的字符串中恢复id
     */
    public static function recoveryId($padded, $number_only = false)
    {
        if ($number_only) {
            $numericMapFlip = array_flip(self::$numericMap);
        } else {
            $numAlphaMapFlip = ['odd' => array_flip(self::$numAlphaMap['odd']), 'even' => array_flip(self::$numAlphaMap['even'])];
        }
        //逐位恢复
        $len = strlen($padded);
        for ($i = 0; $i < $len; $i++) {
            $tmp = $number_only ? $numericMapFlip[$padded[$i]] : $numAlphaMapFlip[$i % 2 == 0 ? 'even' : 'odd'][$padded[$i]];
            $tmp -= $i;
            $padded[$i] = $tmp >= 0 ? $tmp % 10 : 10 + $tmp % 10;
        }

        return (int)$padded;
    }
}