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
    protected static $type;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function script()
    {
        $url = route('pay_retry', ['operation' => static::$type, 'id' => '']);
        Admin::script(<<<SCRIPT
$('.grid-retry').on('click', function () {
    $(this).prop('disabled', true);
    var btn = $(this);
    $.post('$url/' + btn.data('id'),function(data){
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

SCRIPT
        );
    }

    abstract function reDo();

    public function __toString()
    {
        return $this->render();
    }

    protected function render()
    {
        return "<a class='btn btn-xs btn-warning fa grid-retry' data-id='{$this->id}'>重试</a>";
    }

    protected function response($success, $msg)
    {
        return ['status' => $success, 'msg' => $msg];
    }
}