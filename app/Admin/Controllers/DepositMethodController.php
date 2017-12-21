<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\DepositInterface;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Platform;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DepositMethodController extends Controller
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
        return Admin::grid(DepositMethod::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->column('title', '充值方式');
            $grid->column('platform.name', '支付平台');
            $grid->column('memo', '备注');
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
        return Admin::form(DepositMethod::class, function (Form $form) {
            $form->text('title', '支付方式')->placeholder('如扫码支付,银行卡支付..')->rules('required|max:255', ['required' => '必填项']);
            $form->select('platform_id', '所属平台')->options(Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }))->rules('required', ['required' => '必须选择所属平台']);
            $form->text('impl', '实现路径')->rules('required|max:255', ['required' => '必填项']);
            $form->text('memo', '备注')->rules('nullable');

            $form->textarea('config', '接口参数')->rules('nullable');
            $form->saving(function (Form $form) {
                if ($form->impl && !is_subclass_of($form->impl, DepositInterface::class)) {
                    throw new \Exception("储值接口 {$form->impl} 未实现");
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
