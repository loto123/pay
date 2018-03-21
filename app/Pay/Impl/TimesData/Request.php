<?php

namespace App\Pay\Impl\TimesData;
use App\Pay\RSA;

/**
 * 请求报文结构
 * Author: huangkaixuan
 * Date: 2018/3/8
 * Time: 17:33
 */
class Request extends Message
{

    const ENCRYPT_NONE = 0;
    const ENCRYPT_RSA = 1; //不加密
    /**
     * 消息体加密算法
     * @var int
     */
    private $encryptAlgo = self::ENCRYPT_NONE; //RSA
    /**
     * 请求接口地址
     * @var string
     */
    private $reqUrl;

    public function __construct($appId, $version, $reqType, $reqUrl, $reqNo, $mchid, $signType = self::SIGN_RSA1)
    {
        $this->headFields = ['appId' => $appId,
            'version' => $version,
            'reqType' => $reqType,
            'reqNo' => $reqNo,
            'mchid' => $mchid,
            'signType' => [self::SIGN_RSA1 => 'RSA1', self::SIGN_MD5 => 'MD5'][$signType]
        ];
        $this->signType = $signType;
        $this->reqUrl = $reqUrl;
    }

    /**
     * 添加主体
     * @param $name
     * @param $value
     */
    public function appendData($name, $value)
    {
        if (!array_key_exists($name, $this->dataFields)) {
            $this->dataFields[$name] = $value;
        }
    }

    /**
     * 发送请求报文
     * @throws \Exception
     * @return Response
     */
    public final function send()
    {
        //数据签名
        $this->appendHead('sign', $this->sign());

        //数据加密
        $data = self::arrayToXML($this->dataFields, null);
        if ($this->encryptAlgo !== self::ENCRYPT_NONE) {
            if ($this->RSAInstance === null) {
                throw new \Exception('未配置RSA加密实例');
            }
            switch ($this->encryptAlgo) {
                case self::ENCRYPT_RSA:
                    $data = $this->RSAInstance->encrypt($data);
                    break;
            }
        }

        return $this->post($this->reqUrl, self::arrayToXML(['head' => $this->headFields, 'data' => $data], 'xml'));
    }

    /**
     * 添加头部
     * @param $name
     * @param $value
     */
    protected function appendHead($name, $value)
    {
        if (!array_key_exists($name, $this->headFields)) {
            $this->headFields[$name] = $value;
        }
    }

    /**
     * 数组转为XML
     * @param array $array
     * @param string $rootNode
     * @return string
     */
    public static function arrayToXML(array $array, $rootNode)
    {
        if ($rootNode !== null) {
            $array = [$rootNode => $array];
        }
        array_walk($array, function (&$value, $nodeName) {
            $value = "<$nodeName>" . (is_array($value) ? self::arrayToXML($value, null) : strval($value)) . "</$nodeName>";
        });

        return implode(array_values($array));
    }

    /**
     * POST数据到接口
     * @param $url
     * @param $xml
     * @return Response
     */
    private function post($url, $xml)
    {
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'content-type:text/xml',
                'content' => $xml
            )
        );
        dump($xml);

        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        return self::parseResponse($response, $this->signType, $this->RSAInstance);
    }

    /**
     * 解析响应数据
     * @param $str
     * @return Response
     * @throws \Exception
     */
    public static function parseResponse($str, $signType, RSA $RSAInstance = null)
    {
        $xmlObj = simplexml_load_string($str);
        if ($xmlObj === false) {
            throw new \Exception('无效响应:' . $str);
        }

        $response = json_decode(json_encode($xmlObj, JSON_UNESCAPED_UNICODE), true);

        foreach ($response['head'] as &$value) {
            if (empty($value)) {
                $value = '';
            }
        }

        if (array_key_exists('data', $response)) {
            foreach ($response['data'] as &$value) {
                if (empty($value)) {
                    $value = '';
                }
            }
        }

        //dump($response);

        $responseObj = new Response($response['head']['respCd'], $response['head']['respMsg'], $response['head']['reqNo'], $response['head']['respNo']);
        $responseObj->dataFields = array_key_exists('data', $response) ? $response['data'] : [];
        $responseObj->signType = $signType;
        if ($signType == Message::SIGN_RSA1) {
            $responseObj->setRSAInstance($RSAInstance);
        }

        if ($responseObj->verify($response['head']['sign'])) {
            throw new \Exception('响应报文签名校验错误' . json_encode($response));
        }
        return $responseObj;
    }

    /**
     * 设置报文加密算法
     * @param $algo integer
     */
    protected function setEncryptAlgo($algo)
    {
        $this->encryptAlgo = $algo;
    }
}