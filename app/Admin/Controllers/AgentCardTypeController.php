<?php

namespace App\Admin\Controllers;

use App\Agent\CardType;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class AgentCardTypeController extends Controller
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

            $content->header('VIP卡管理');
            $content->description('卡类型');

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
        return Admin::grid(CardType::class, function (Grid $grid) {
            $grid->name('卡名');
            $grid->percent('分润比例')->display(function ($value) {
                return "$value%";
            });
            $grid->valid_days('有效期')->display(function ($days) {
                return $days > 0 ? "{$days}天" : '永久';
            });
            $grid->created_at('添加时间');
            $grid->updated_at('修改时间');
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('VIP卡管理');
            $content->description('类型编辑');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(CardType::class, function (Form $form) {
            $form->text('name', '卡名')->rules('min:2', ['min' => '最少2个字符']);
            $form->rate('percent', '分润比例(百分比)')->placeholder('%')->rules('required');
            $form->number('valid_days', '有效期(填写天数,从开通vip开始算,0永久)')->placeholder('永久填0')->rules('between:0,2000|required');
            $form->saving(function (Form $form) {
                $max_percent = 100 - config('default_agent_ratio', 0);
                if ($form->percent > $max_percent) {
                    throw new \Exception('分润比例最大值为' . $max_percent);
                }
            });
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('VIP卡管理');
            $content->description('添加新卡');

            $content->body($this->form());
        });
    }
}
