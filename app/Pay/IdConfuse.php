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
    const RANDOM_SEED = 0x8E7EAFE8;
    private static $hexDecMap = [
        '0' => '0', '1' => '1', '2' => '2', '3' => '3',
        '4' => '4', '5' => '5', '6' => '6', '7' => '7',
        '8' => '8', '9' => '9', 'a' => '10', 'b' => '11',
        'c' => '12', 'd' => '13', 'e' => '14', 'f' => '15',
    ];

    /**
     * 混淆id
     * @param $id int 需要混淆的id
     * @pram $padTo int 填充到指定长度,默认不填充
     * @param $number_only boolean 是否仅允许数字,数字总是以1开头
     * @return string 混淆后的字符串
     */
    public static function mixUpDepositId($id, $padTo = 0, $number_only = false)
    {
        $padTo = min($padTo, 50) - ($number_only ? 1 : 0);//最多填充为50个字符

        $mixed = sprintf('%u', self::RANDOM_SEED ^ $id);

        if (!$number_only) {
            $mixed = dechex($mixed);
        }

        //填充长度
        $pad_length = $padTo - strlen($mixed) - 2;
        $pad_length = $pad_length > 0 ? $pad_length : 0;

        $result = '';
        if ($pad_length > 0) {
            $checksum = self::checksum($mixed, $number_only);

            $result = substr($checksum, 0, $pad_length);
        }

        //填充 + 混淆值 + 2字符填充长度
        $result .= $mixed . sprintf('%02d', $pad_length);

        if ($number_only) {
            $result = "1$result";
        }

        return $result;
    }

    private static function checksum($string, $number_only)
    {
        $checksum = md5($string);
        return $number_only ? self::hex2dec($checksum) : $checksum;
    }

    private static function hex2dec($string)
    {
        $len = strlen($string);
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= self::$hexDecMap[$string[$i]];
        }
        return $out;
    }

    /**
     * 恢复id
     * @param $mixed
     * @param bool $number_only
     * @return int 从混淆的字符串中恢复id
     */
    public static function recoveryDepositId($mixed, $number_only = false)
    {
        if ($number_only) {
            $mixed = substr($mixed, 1);
        }

        $pad_length = (int)substr($mixed, -2);
        $mixed_length = strlen($mixed) - 2 - $pad_length;
        $pad = substr($mixed, 0, $pad_length);
        $mixed = substr($mixed, $pad_length, $mixed_length);

        if ($pad_length == 0 || substr(self::checksum($mixed, $number_only), 0, $pad_length) === $pad) {
            if (!$number_only) {
                $mixed = hexdec($mixed);
            }

            $mixed = (int)$mixed;

            return $mixed ^ self::RANDOM_SEED;

        } else {
            //dump([substr($hash_alrigm($mixed), 0, $pad_length - $number_only ? 1:0), substr($pad, $number_only ? 1:0)]);
            return 0;
        }
    }
}