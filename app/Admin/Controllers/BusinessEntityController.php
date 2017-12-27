<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\BusinessEntity;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class BusinessEntityController extends Controller
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

            $content->header('支付管理');
            $content->description('签约主体');

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
        return Admin::grid(BusinessEntity::class, function (Grid $grid) {
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('company_name', '公司名称');
            });
            $grid->id('ID')->sortable();
            $grid->column('company_name', '公司名');
            $grid->column('open_bank', '开户行');
            $grid->column('bank_account', '对公账户')->display(function ($bank_account) {
                $cover_len = strlen($bank_account) - 4;
                return substr_replace($bank_account, str_repeat('*', $cover_len), 0, $cover_len);
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

            $content->header('支付管理');
            $content->description('编辑签约主体');

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
        return Admin::form(BusinessEntity::class, function (Form $form) {
            $form->text('company_name', '公司名')->rules('between:3,20', ['between' => '3~20个字符']);
            $form->text('bank_account', '对公账户')->rules('between:8,28', ['between' => '请填写8~28位长对公账户']);
            $form->text('open_bank', '开户行')->rules('between:5,50', ['between' => '请填写正确的开户行']);
            $form->setWidth(4, 2);
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

            $content->header('支付管理');
            $content->description('添加签约主体');

            $content->body($this->form());
        });
    }
}
