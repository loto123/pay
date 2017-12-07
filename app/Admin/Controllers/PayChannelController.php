<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\Model\BusinessEntity;
use App\Pay\Model\Channel;
use App\Pay\Model\Platform;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class PayChannelController extends Controller
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
        return Admin::grid(Channel::class, function (Grid $grid) {
            $grid->model()->orderBy('disabled');
            $grid->id('ID')->sortable();
            $grid->platform()->name('支付平台');//支付平台
            $grid->column('businessEntity.company_name', '签约主体');//签约主体

            //开启状态
            $grid->disabled('通道状态')->switch([
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
        return Admin::form(Channel::class, function (Form $form) {
            $form->select('platform_id', '支付平台')->options(Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }))->rules('required', ['required' => '必须选择支付平台']);

            $form->select('entity_id', '签约主体')->options(BusinessEntity::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['company_name']];
            }))->rules('required', ['required' => '必须选择签约主体']);

            $form->textarea('cfg', '通道参数')->placeholder("支付通道接口参数,如key,秘钥等一行一个:\r\n参数1=值1\r\n参数2=值2..")->rules('required|max:255', ['required' => '必须填写参数', 'max' => '最多255字符']);
            $form->switch('disabled', '通道状态')->states([
                'on' => ['value' => 1, 'text' => '禁用', 'color' => 'danger'],
                'off' => ['value' => 0, 'text' => '启用', 'color' => 'success'],
            ]);
            $form->setWidth(6, 2);
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
