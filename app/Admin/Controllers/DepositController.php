<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\ChargeRetry;
use App\Pay\Model\Deposit;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DepositController extends Controller
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

            $content->header('充值记录');
            //$content->description('description');

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
        return Admin::grid(Deposit::class, function (Grid $grid) {
            $grid->model()->has('masterContainer.user')->with('masterContainer.user');

            //工具按钮
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                if ($actions->row['state'] == Deposit::STATE_CHARGE_FAIL) {
                    $actions->append(new ChargeRetry($actions->getKey()));
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
            $grid->amount('充值金额');
            $grid->channel()->name('支付通道');
            $grid->method()->title('支付方式');
            $grid->state('状态')->display(function ($value) {
                $class = 'default';
                switch ($value) {
                    case Deposit::STATE_UNPAID:
                        $class = 'default';
                        break;
                    case Deposit::STATE_COMPLETE:
                        $class = 'success';
                        break;
                    default:
                        $class = 'danger';
                }
                $state = Deposit::getStateText($value);

                return "<span class=\"label label-$class\">$state</span>";
            });

        });
    }
}
