<?php

namespace App\Notifications;

/**
 * 用户消息确认回调接口
 * Author: huangkaixuan
 * Date: 2018/1/16
 * Time: 14:51
 */

interface UserConfirmCallback
{
    /**
     * 根据用户选项执行业务逻辑
     * @param $selected_value string 用户选择值
     * @param array $user_data array 发送消息时的自定义数据,原样返回
     * @return ConfirmExecuteResult
     */
    public function confirm($selected_value, $user_data = []);
}