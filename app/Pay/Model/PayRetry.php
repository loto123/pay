<?php
/**
 * 支付重试基类
 * Author: huangkaixuan
 * Date: 2017/12/23
 * Time: 18:55
 */

namespace App\Pay\Model;

use Encore\Admin\Admin;

abstract class PayRetry
{
    protected $type = 'charge';

    public function __construct($id)
    {
        $this->id = $id;
    }

    abstract function reDo();

    public function __toString()
    {
        return $this->render();
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<a class='btn btn-xs btn-warning fa grid-retry' data-id='{$this->id}'>重试</a>";
    }

    protected function script()
    {
        $url = route('pay_retry', ['operation' => $this->type, 'id' => $this->id]);
        $token = csrf_token();
        return <<<SCRIPT
        $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '$token'
    }
});
$('.grid-retry').on('click', function () {
    $(this).prop('disabled', true);
    var btn = $(this);
    $.post('$url',function(data){
        if (data.status === true) {
            toastr.success(data.msg);
            btn.removeClass('btn-warning').addClass('btn-success').text('操作成功');
            $.pjax.reload('#pjax-container');
        } else {
            toastr.error('操作失败:' + data.msg);
            btn.prop('disabled', false);
        }
        
        
    }, 'json');
});

SCRIPT;
    }

    protected function response($success, $msg)
    {
        return ['status' => $success, 'msg' => $msg];
    }
}