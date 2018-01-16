<?php

namespace App\Notifications;

use Exception;

/**
 * UserConfirmCallback接口返回格式
 * Author: huangkaixuan
 * Date: 2018/1/16
 * Time: 15:14
 */


/**
 * 业务执行结果
 * Class ConfirmExecuteResult
 * @package App\Notifications
 */
class ConfirmExecuteResult
{
    const EXECUTE_FAIL = 0;//执行失败
    const EXECUTE_SUCCESS = 1;//执行成功

    /**
     * 执行结果可读消息
     * @var string
     */
    public $message;

    /**
     * 执行结果
     * @var int
     */
    public $result;

    /**
     * 执行异常
     * @var Exception
     */
    public $exception;

    /**
     * 业务执行结果代码
     * @var int
     */
    private $code;

    private function __construct($message, $result, Exception $e = null)
    {
        $this->result = $result;
        $this->message = $message;
        $this->exception = $e;
    }

    /**
     * 返回失败结果
     * @param $message string 失败文本
     * @param Exception|null $e 异常
     */
    public static function fail($message, Exception $e = null)
    {
        return new self($message, self::EXECUTE_FAIL, $e);
    }

    /**
     * 返回成功结果
     * @param $message string 提示文本
     */
    public static function success($message)
    {
        return new self($message, self::EXECUTE_SUCCESS, null);
    }

    /**
     * get code
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * set code
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}