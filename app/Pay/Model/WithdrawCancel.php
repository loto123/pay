<?php
/**
 * 尝试取消提现
 * Author: huangkaixuan
 * Date: 2017/12/23
 * Time: 19:45
 */

namespace App\Pay\Model;

use Encore\Admin\Admin;

class WithdrawCancel extends PayRetry
{
    public static $abnormal_states = [Withdraw::STATE_SEND_FAIL, Withdraw::STATE_PROCESS_FAIL];

    public static function script()
    {
        $url = route('withdraw_cancel', ['withdraw' => '']);
        Admin::script(<<<SCRIPT
$('.grid-cancel-withdraw').on('click', function () {
    $(this).prop('disabled', true);
    var btn = $(this);
    $.post('$url/' + btn.data('id'),function(data){
        if (data.status === true) {
            toastr.success('操作成功');
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

    function reDo()
    {
    }

    protected function render()
    {
        return "<a class='btn btn-xs btn-default fa grid-cancel-withdraw' data-id='{$this->id}'>取消</a>";
    }
}