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
            $grid->id('ID')->sortable();
            $grid->column('name', '平台')->editable();
            $grid->depositMethods('充值方式')->display(function ($depositMethods) {

                return implode('&nbsp;', array_map(function ($value) {
                    return '<span class="label label-success">' . $value . '</span>';
                }, array_column($depositMethods, 'title')));
            });

            $grid->withdrawMethods('提现方式')->display(function ($withdrawMethods) {

                return implode('&nbsp;', array_map(function ($value) {
                    return '<span class="label label-primary">' . $value . '</span>';
                }, array_column($withdrawMethods, 'title')));
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
            //$form->text('impl', '实现路径')->rules('between:5,255', ['between' => '必填,不超过255个字符']);
            //$form->textarea('public_cfg', '公共参数')->rules('nullable|max:255', ['max' => '不能超过255个字符']);;
//            $form->saving(function (Form $form) {
//                if (!class_exists($form->impl)) {
//                    throw new \Exception("平台接口 {$form->impl} 不存在");
//                }
//            });
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
