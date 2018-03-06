<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\MasterContainer;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawCancel;
use App\Pay\Model\WithdrawRetry;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('出售记录');
            //$content->description('description');
            $content->row(function (Row $row) {
                //累计已提现
                $withdrawed = Withdraw::has('masterContainer.user')->where('state', '<>', Withdraw::STATE_CANCELED)->sum('amount');
                //累计待提现
                $waitToWithdraw = MasterContainer::has('user')->sum('balance');
                $row->column(3, "<h4>累计销售额:<span style='color:#FFAE20;font-weight:bold;'>$withdrawed</span>元</h4>");
                $row->column(3, "<h4>累计待销售:<span style='color:#FFAE20;font-weight:bold;'>$waitToWithdraw</span>元</h4>");
            });


            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Withdraw::class, function (Grid $grid) {
            $token = csrf_token();
            $exception_url = 'withdraw-exceptions';
            Admin::script(<<<SCRIPT
                var exceptions_down = {};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '$token'
                    }
                });
                
                var exception_row = $('<tr><td colspan="10" style="text-align:center;"></td></tr>').hide();
;

//单据弹层
$('.bill_info').each(function(){
$(this).popover(
{
    html:true,
    content:'<p>商户订单号:<br/><input type="text" readonly value ="'+$(this).data('bill') +'"/></p><p>内部ID:<span class="text-danger">(仅限内部查询,刮开查看)</span><span style="cursor:text;background-color:#ccc;color:#ccc;display: block;text-indent:1em;">'+$(this).data('inner_id')+'</span></p>',
    title:'凭据信息',
    placement:'top'
}
);});

//异常记录
$('.exception-detail').click(function () {
    $(this).toggleClass('detail-open');
    if ($(this).hasClass('detail-open')) {
        $(this).parent().parent().after(exception_row);
        exception_row.children(0).html('加载中...');
        exception_row.show();
        $.get('$exception_url', {id: $(this).data('id')}, function (json) {
            var table = "<table class=\"table table-hover table-condensed\"><thead><tr class=\"bg-primary\"><td>提交时间</td><td>接口返回</td><td>异常信息</td><td>提现结果</td></tr></thead><tbody>";
            for (var i in json) {
                var row = json[i];

                table += '<tr class="danger"><td>' + row.created_at + '</td><td>' + row.message + '</td><td>' + row.exception + '</td><td>' + row.state + '</td></tr>';
            }
            table += '</tbody></table>';
            exception_row.children(0).html(table);
        }, 'json');
    } else {
        exception_row.hide();
    }
});
SCRIPT

            );

            WithdrawRetry::script();
            WithdrawCancel::script();

            $grid->model()->orderBy('id', 'desc')->has('masterContainer.user')->with(['masterContainer.user', 'petSellBill']);
            //工具按钮
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();

                if (in_array($actions->row['state'], WithdrawRetry::$abnormal_states)) {
                    $actions->append(new WithdrawRetry($actions->getKey()));
                    $actions->append('&nbsp;&nbsp;');
                    $actions->append(new WithdrawCancel($actions->getKey()));
                    $show_exceptions = true;
                } else {
                    $show_exceptions = $actions->row['state'] == Withdraw::STATE_CANCELED;
                }

                if ($show_exceptions) {
                    $actions->append("&nbsp;&nbsp;<a class='btn btn-xs btn-warning fa exception-detail' data-id='{$actions->getKey()}'>异常记录</a>");
                }
            });
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            //筛选
            $grid->filter(function ($filter) {

                // 在这里添加字段过滤器
                $filter->disableIdFilter();
                $filter->between('updated_at', '时间')->datetime();

                $filter->where(function ($query) {
                    $query->whereHas('masterContainer.user', function ($query) {
                        $query->where('mobile', '=', $this->input);
                    });

                }, '用户ID');
            });
            $grid->column('user_id', '用户ID')->display(function () {
                return $this->masterContainer->user->mobile;
            });
            $grid->column('nickname', '昵称')->display(function () {
                return $this->masterContainer->user->name;
            });
            $grid->updated_at('时间');
            $grid->amount('售价');
            $grid->column('pet', '宠物编号')->display(function () {
                return $this->petSellBill ? $this->petSellBill->pet_id : '';
            });
            $grid->column('actual', '实际到账')->display(function () {
                return sprintf('%.2f', $this->amount - $this->system_fee);
            });
            $grid->channel()->name('支付通道');
            $grid->method()->title('提现方式');
            $grid->state('状态')->display(function ($value) {
                $class = 'default';
                switch ($value) {
                    case Withdraw::STATE_QUEUED:
                    case Withdraw::STATE_SUBMIT:
                        $class = 'info';
                        break;
                    case Withdraw::STATE_COMPLETE:
                        $class = 'success';
                        break;
                    case Withdraw::STATE_CANCELED:
                        $class = 'default';
                        break;
                    default:
                        $class = 'danger';
                }
                $state = Withdraw::getStateText($value);

                return "<span class=\"label label-$class\">$state</span>";
            });
            $grid->method()->impl('凭据单号')->display(function ($impl) {
                $bill_no = (new $impl)->mixUpWithdrawId($this->getKey());
                $id = $this->getKey();
                return '<a class="btn btn-primary bill_info" role="button" data-toggle="popover" data-bill="' . $bill_no . '" data-inner_id="' . $id . '">查看</a>';

            });

        });
    }

    /**
     * 输出提现异常列表
     * @param $id
     */
    public function exception_view(Request $request)
    {
        $exceptions = Withdraw::find($request->id)->exceptions()->get();
        if ($exceptions) {
            $exceptions->map(function (&$item) {
                $item['state'] = Withdraw::getStateText($item['state']);
            });
            return $exceptions;
        }
    }
}
