<?php

namespace App\Pay;

class Crypt3Des
{

    public $key = "";

    /*构造方法*/
    public function encrypt($input)
    { // 数据加密
        if (empty($input)) {
            return null;
        }
        $dsd = openssl_encrypt($input, 'des-ede3', $this->key);
        $data = base64_decode($dsd);
        return $this->strToHex($data);
    }

    private function strToHex($string)
    {
        $hex = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $iHex = dechex(ord($string[$i]));
            if (strlen($iHex) == 1)
                $hex .= '0' . $iHex;
            else
                $hex .= $iHex;
        }
        $hex = strtoupper($hex);
        return $hex;
    }

    /*private function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
     
    private function pkcs5_unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text )) {
            return false;
        }
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad) {
            return false;
        }
        return substr ( $text, 0, - 1 * $pad );
    }
     
    private function PaddingPKCS7($data) {
        $block_size = mcrypt_get_block_size ( MCRYPT_3DES, MCRYPT_MODE_CBC );
        $padding_char = $block_size - (strlen ( $data ) % $block_size);
        $data .= str_repeat ( chr ( $padding_char ), $padding_char );
        return $data;
    }*/

    public function decrypt($encrypted)
    { // 数据解密
        if (!$encrypted || empty($encrypted)) {
            return null;
        }
        $encrypted = $this->hexToStr($encrypted);
        $encrypted = base64_encode($encrypted);
        if (!$encrypted || empty($encrypted)) {
            return null;
        }
        $data = openssl_decrypt($encrypted, 'des-ede3', $this->key);
        return $data;
    }

    private function hexToStr($hex)
    {
        $string = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
}