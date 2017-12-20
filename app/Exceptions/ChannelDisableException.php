<?php
/**
 * 支付通道禁用异常
 * Author: huangkaixuan
 * Date: 2017/12/11
 * Time: 10:30
 */

namespace App\Exceptions;

use Exception;

class ChannelDisableException extends Exception
{
    private $from_form;

    public function __construct($message, $from_form)
    {
        parent::__construct($message);
        $this->from_form = $from_form;
    }

    public function render()
    {
        if (!$this->from_form) {
            return ['status' => false, 'message' => $this->getMessage()];
        }
    }
}