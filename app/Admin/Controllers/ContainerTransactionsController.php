<?php
/**
 * 交易商交易记录
 */

namespace App\Admin\Controllers;

use App\Admin\Model\ContainerTransaction;
use App\Http\Controllers\Controller;
use App\Pay\Model\Transfer;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ContainerTransactionsController extends Controller
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('容器交易记录');
            $content->description('点击筛选查询容器交易');
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
        return Admin::grid(ContainerTransaction::class, function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'asc');
            //工具按钮
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
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
                $filter->equal('container_id', '容器ID');
                $filter->equal('container_type', '容器类型')->radio([
                    '' => '所有',
                    'master' => '主容器',
                    'settle' => '结算',
                ]);
                $filter->equal('state', '状态')->radio([
                    '' => '所有',
                    Transfer::STATE_COMPLETE => '生效',
                    Transfer::STATE_CHARGEBACK => '撤销',
                ]);
                $filter->equal('pay_type', '交易类型')->select(ContainerTransaction::getTypeTextArray());

                $filter->between('created_at', '时间')->datetime();

            });

            $grid->container_id('容器ID');
            $grid->amount('金额');
            $grid->container_type('容器类型')->display(function ($value) {
                return $value === 'master' ? '主容器' : '结算';
            });
            $grid->is_frozen('冻结')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->pay_type('交易类型')->display(function ($value) {
                switch ($value) {
                    case ContainerTransaction::TYPE_FREEZE:
                        $class = 'label-info';
                        break;
                    case ContainerTransaction::TYPE_UNFREEZE:
                        $class = 'label-warning';
                        break;
                    case ContainerTransaction::TYPE_TRANSFER_OUT:
                    case ContainerTransaction::TYPE_WITHDRAW:
                    case ContainerTransaction::TYPE_MONEY_EXTRACT:
                        $class = 'label-primary';
                        break;
                    case ContainerTransaction::TYPE_DEPOSIT:
                    case ContainerTransaction::TYPE_PROFIT_SHARE:
                    case ContainerTransaction::TYPE_TRANSFER_IN:
                        $class = 'label-success';
                        break;

                }
                return '<span class="label ' . $class . '">' . ContainerTransaction::getTypeTextArray()[$value] . '</span>';
            });
            $grid->created_at('交易时间');
            $grid->state('撤销')->display(function ($value) {
                return $value == 2 ? '是' : '否';
            });

        });
    }
}
