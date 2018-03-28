<?php
/**
 * 苹果内购
 * User: wangtao
 * Date: 2016/12/28
 * Time: 22:39
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\PayLogger;

class ItunesReceiptValidator
{

    private $sand_box_url = 'https://sandbox.itunes.apple.com/verifyReceipt';
    private $production_url = 'https://buy.itunes.apple.com/verifyReceipt';

    private $receipt = null;
    private $endpoint = null;

    function __construct($is_sand_box, $receipt = NULL)
    {
        if ($is_sand_box) {
            $url = $this->sand_box_url;
        } else {
            $url = $this->production_url;
        }

        $this->setEndPoint($url);
        if ($receipt) {
            $this->setReceipt($receipt);
        }
    }

    function getEndpoint()
    {
        return $this->endpoint;
    }

    function setEndPoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    function validateReceipt()
    {
        $response = $this->makeRequest();
        $decoded_response = $this->decodeResponse($response);
        if (!isset($decoded_response->status) || $decoded_response->status != 0) {
            PayLogger::deposit()->error('苹果支付验证错误', ['status' => $decoded_response->status]);
            return false;
//            throw new Exception('Invalid receipt. Status code: ' . (!empty($decoded_response->status) ? $decoded_response->status : 'N/A'));
        }
        if (!is_object($decoded_response)) {
            PayLogger::deposit()->error('苹果支付响应无效', ['response' => $decoded_response]);
            return false;
        }
        return $decoded_response->receipt;
    }

    private function makeRequest()
    {
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodeRequest());
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if ($errno != 0) {
            PayLogger::deposit()->error('苹果验证发起失败', ['error' => $errmsg]);
            return false;
        }
        return $response;
    }

    private function encodeRequest()
    {
        return json_encode(array('receipt-data' => $this->getReceipt()));
    }

    function getReceipt()
    {
        return $this->receipt;
    }

    function setReceipt($receipt)
    {
        if (strpos($receipt, '{') !== false) {
            $this->receipt = base64_encode($receipt);
        } else {
            $this->receipt = $receipt;
        }
    }

    private function decodeResponse($response)
    {
        return json_decode($response);
    }

}