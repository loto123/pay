<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\Platform;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class PayPlatformController extends Controller
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
        return Admin::grid(Platform::class, function (Grid $grid) {
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('name', '平台')->editable();
            $grid->depositMethods('充值方式')->display(function ($depositMethods) {
                $methods = array_column($depositMethods, 'disabled', 'title');
                array_walk(
                    $methods, function (&$disabled, $title) {
                    $disabled = '<span class="label label-' . ($disabled ? 'danger' : 'success') . '">' . $title . '</span>';
                });
                return implode('&nbsp;', $methods);
            });

            $grid->withdrawMethods('提现方式')->display(function ($withdrawMethods) {
                $mthods = array_column($withdrawMethods, 'disabled', 'title');
                array_walk(
                    $mthods, function (&$disabled, $title) {
                    $disabled = '<span class="label label-' . ($disabled ? 'danger' : 'success') . '">' . $title . '</span>';
                });
                return implode('&nbsp;', $mthods);
            });

            $grid->actions(function ($actions) {
                $actions->disableEdit();
            });


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
        return Admin::form(Platform::class, function (Form $form) {
            $form->text('name', '平台名')->rules('between:2,10', ['between' => '填写2~10个字符']);
//            $form->checkbox('banksSupport', '支持银行')->options(Bank::all()->mapWithKeys(function ($item) {
//                return [$item['id'] => $item['name']];
//            }));
            $form->setWidth(4, 2);
            $form->disableReset();
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
