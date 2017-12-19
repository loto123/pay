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

    /**
     * 混淆id
     * @param $id int 需要混淆的id
     * @pram $padTo int 填充到指定长度,默认不填充
     * @param $number_only boolean 是否仅允许数字
     * @return string 混淆后的字符串
     */
    public static function mixUpDepositId($id, $padTo = 0, $number_only = false)
    {
        $padTo = min($padTo, 50);//最多填充为50个字符

        $mixed = sprintf('%u', self::RANDOM_SEED ^ $id);

        if (!$number_only) {
            $mixed = dechex($mixed);
        }

        //填充长度
        $pad_length = $padTo - strlen($mixed) - 2;
        $pad_length = $pad_length > 0 ? $pad_length : 0;

        $result = '';
        if ($pad_length > 0) {
            $checksum = $number_only ? crc32($mixed) : md5($mixed);
            $result = substr($checksum, 0, $pad_length);
        }

        //填充 + 混淆值 + 2字符填充长度
        $result .= $mixed . sprintf('%02d', $pad_length);
        return $result;
    }

    /**
     * 恢复id
     * @param $mixed
     * @param bool $number_only
     * @return int 从混淆的字符串中恢复id
     */
    public static function recoveryDepositId($mixed, $number_only = false)
    {
        $pad_length = (int)substr($mixed, -2);
        $mixed_length = strlen($mixed) - 2 - $pad_length;
        $pad = substr($mixed, 0, $pad_length);
        $mixed = substr($mixed, $pad_length, $mixed_length);

        $hash_alrigm = $number_only ? 'crc32' : 'md5';
        if ($pad_length == 0 || substr($hash_alrigm($mixed), 0, $pad_length) === $pad) {
            if (!$number_only) {
                $mixed = hexdec($mixed);
            }

            $mixed = (int)$mixed;

            return $mixed ^ self::RANDOM_SEED;

        } else {
            return 0;
        }
    }
}