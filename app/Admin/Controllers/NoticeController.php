<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\ProfitApply;
use App\Notifications\UserApply;
use App\User;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemApply;

class NoticeController extends Controller
{
    use ModelForm;

    /*
     * 添加通知
     * @param $user_id_arr array 消息接受者user_id
     * @param $type int 消息类型 1：分润通知，2：新用户注册通知，3：系统通知
     * @param $content string 消息内容
     * @param $title string 消息标题
     * @param $param string 相关参数 当type=1时为必填，表示分润id
     * @param $operators array 包含字段：
     *        string callback_method 回调函数
     *        array callback_params 回调参数
     *        string expire_time 授权过期时间 秒
     *        array options 操作选项
     * */
    public static function send(array $user_id_arr, $type, $content, $title='',$param='',array $operators=[])
    {
        $data = [
            'type' => $type,
            'content' => $content,
            'param' => $param
        ];
        $notice_data = '';

        if(!empty($operators)){
            $data['operators'] = serialize($operators);
        }

        switch ($type) {
            case 1:
                if(empty($param)) {
                    return false;
                }
                $data['title'] = empty($title)?'分润通知':$title;
                $notice_data = new ProfitApply($data);
                break;
            case 2:
                $data['title'] = empty($title)?'新用户注册通知':$title;
                $notice_data = new UserApply($data);
                break;
            case 3:
                $data['title'] = empty($title)?'系统通知':$title;
                $notice_data = new SystemApply($data);
                break;
        }
        $users = User::whereIn('id',$user_id_arr)->get();
        try {
            Notification::send($users, $notice_data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
