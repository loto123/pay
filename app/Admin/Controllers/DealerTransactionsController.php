<?php
/**
 * 交易商交易记录
 */

namespace App\Admin\Controllers;

use App\Admin\Model\DealerTransaction;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DealerTransactionsController extends Controller
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('交易商交易记录');
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
        return Admin::grid(DealerTransaction::class, function (Grid $grid) {
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
                $filter->equal('from_uid', '交易商ID');
                $filter->equal('to_uid', '交易对象ID');
                $filter->between('create_time', '时间')->datetime();

            });

            $grid->from_user('交易商')->display(function () {
                return <<<EOT
            <div style="width:140px;height:60px;">
            <img style="width:60px;height:60px;margin:auto 10px;border-radius:50%;float:left;" src="{$this->from_avatar}"/>
            <div style="float:left;width:60px;height:60px;word-break:keep-all; white-space:nowrap; ">{$this->from_name}<br/>ID:{$this->from_uid}</div>
</div>
EOT;
            });

            $grid->create_time('时间');
            $grid->price('金额');
            $grid->pet_id('宠物编号');
            $grid->trade_type('类型');
            $grid->to_user('交易对象')->display(function () {
                return <<<EOT
            <div style="width:140px;height:60px;">
            <img style="width:60px;height:60px;margin:auto 10px;border-radius:50%;float:left;" src="{$this->to_avatar}"/>
            <div style="float:left;width:60px;height:60px;word-break:keep-all; white-space:nowrap; ">{$this->to_name}<br/>ID:{$this->to_uid}</div>
</div>
EOT;
            });
        });
    }
}
