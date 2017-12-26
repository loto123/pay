<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\MasterContainer;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawRetry;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

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

            $content->header('提现记录');
            //$content->description('description');
            $content->row(function (Row $row) {
                //累计已提现
                $withdrawed = Withdraw::has('masterContainer.user')->sum('amount');
                //累计待提现
                $waitToWithdraw = MasterContainer::has('user')->sum('balance');
                $row->column(3, "<h4>累计已提现:<span style='color:#FFAE20;font-weight:bold;'>$withdrawed</span>元</h4>");
                $row->column(3, "<h4>累计待提现:<span style='color:#FFAE20;font-weight:bold;'>$waitToWithdraw</span>元</h4>");
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
            $grid->model()->orderBy('id', 'desc')->has('masterContainer.user')->with('masterContainer.user');

            //工具按钮
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                if (in_array($actions->row['state'], WithdrawRetry::$abnormal_states)) {
                    $actions->append(new WithdrawRetry($actions->getKey()));
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
                        $query->where('id', '=', $this->input);
                    });

                }, '用户ID');
            });
            $grid->column('user_id', '用户ID')->display(function () {
                return $this->masterContainer->user->getKey();
            });
            $grid->column('nickname', '昵称')->display(function () {
                return $this->masterContainer->user->name;
            });
            $grid->updated_at('时间');
            $grid->amount('提现金额');
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
                    default:
                        $class = 'danger';
                }
                $state = Withdraw::getStateText($value);

                return "<span class=\"label label-$class\">$state</span>";
            });

        });
    }
}
