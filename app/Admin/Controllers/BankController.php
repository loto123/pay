<?php

namespace App\Admin\Controllers;

use App\Bank;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;

class BankController extends Controller
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

            $content->header('银行管理');
            $content->description('');

            $content->body($this->grid());
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

            $content->header('编辑银行');
            $content->description('');

            $content->body($this->form()->edit($id));
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
            $content->description('创建银行');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Bank::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->name('银行');
            $grid->logo('图标')->image();
//            $grid->card_num_pre('卡号开头');
//            $grid->card_num_pre_size('卡号开头长度');
            $grid->created_at('添加时间');
            $grid->updated_at('更新时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Bank::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name',  '银行')->rules('required');
            $form->image('logo', '图片')->uniqueName();
//            $form->textarea('card_num_pre','卡号开头')->placeholder('请输入卡号开头，用英文逗号分隔');
//            $form->text('card_num_pre_size','卡号开头长度')->rules('required|min:0|max:8');
            $form->display('created_at', '添加时间');
            $form->display('updated_at', '更新时间');
//            $form->saving(function (Form $form) {
//                if(!empty($form->card_num_pre)) {
//                    try{
//                        $bank_card_pre_list = explode(',',$form->card_num_pre);
//                        foreach ($bank_card_pre_list as $value) {
//                            if(strlen($value) != $form->card_num_pre_size) {
//                                $error = new MessageBag([
//                                    'title'   => '操作失败',
//                                    'message' => '卡号开头中数据的长度需与卡号开头长度保持一致',
//                                ]);
//                                return back()->with(compact('error'));
//                            }
//                        }
//                    } catch (\Exception $e) {
//                        $error = new MessageBag([
//                            'title'   => '操作失败',
//                            'message' => '卡号开头输入有误',
//                        ]);
//                        return back()->with(compact('error'));
//                    }
//                }
//
//            });

        });
    }
}
