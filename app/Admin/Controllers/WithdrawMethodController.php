<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\Platform;
use App\Pay\Model\WithdrawMethod;
use App\Pay\WithdrawInterface;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class WithdrawMethodController extends Controller
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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(WithdrawMethod::class, function (Grid $grid) {
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->id('ID')->sortable();
            $grid->column('title', '提现方式');
            $grid->column('platform.name', '支付平台');
            $grid->column('memo', '备注');
            $grid->column('targetPlatform.name', '提现目标');
            $grid->disabled('状态')->switch([
                'on' => ['value' => 1, 'text' => '禁用', 'color' => 'danger'],
                'off' => ['value' => 0, 'text' => '启用', 'color' => 'success'],
            ]);
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

            $content->header('header');
            $content->description('description');

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
        return Admin::form(WithdrawMethod::class, function (Form $form) {
            $form->text('title', '提现方式')->placeholder('如小额批量付款')->rules('required|max:255', ['required' => '必填项']);
            $form->text('show_label', '展示文本')->placeholder('用于提现方式列表显示')->rules('required|max:10', ['required' => '必填项']);
            $form->select('platform_id', '所属平台')->options(Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }))->rules('required', ['required' => '必须选择所属平台']);

            $form->select('target_platform', '提现到')->options(array_merge([0 => '银行卡'], Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            })->toArray()))->rules('nullable');
            $form->text('impl', '实现路径')->rules('required|max:255', ['required' => '必填项']);
            $form->text('memo', '备注')->rules('nullable');
            $form->textarea('config', '接口参数')->rules('nullable');
            $form->saving(function (Form $form) {
                if ($form->target_platform && !Platform::find($form->target_platform)) {
                    throw new \Exception("无效的提现目标");
                }
                if ($form->impl && !is_subclass_of($form->impl, WithdrawInterface::class)) {
                    throw new \Exception("提现接口 {$form->impl} 未实现");
                }
            });

            $form->switch('disabled', '状态')->states([
                'on' => ['value' => 1, 'text' => '禁用', 'color' => 'danger'],
                'off' => ['value' => 0, 'text' => '启用', 'color' => 'success'],
            ]);
            $form->setWidth(8, 2);
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

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }
}
