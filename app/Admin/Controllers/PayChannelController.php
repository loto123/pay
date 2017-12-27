<?php

namespace App\Admin\Controllers;

use App\Admin\Displayers\SwitchDisplay;
use App\Exceptions\ChannelDisableException;
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

            $content->header('支付管理');
            $content->description('支付通道');

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
        \Encore\Admin\Grid\Column::$displayers["switch"] = SwitchDisplay::class;
        return Admin::grid(Channel::class, function (Grid $grid) {
            $grid->filter(function ($filter) {

                // 在这里添加字段过滤器
                $filter->like('name', '通道名称');

                $filter->equal('entity_id', '签约主体')->select(BusinessEntity::all()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['company_name']];
                }));

                $filter->equal('spare_channel_id', '备用通道')->select(Channel::all()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['name']];
                }));
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->id('ID')->sortable();
            $grid->name('通道名称');
            $grid->platform()->name('支付平台');//支付平台
            $grid->column('businessEntity.company_name', '签约主体');//签约主体
            $grid->column('spareChannel.name', '备用通道');
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

            $content->header('通道编辑');
            $content->description('同一支付平台可签约多条通道');

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
            //通道名
            $form->text('name', '通道名称')->rules('between:1,20', ['between' => '1~20个字符']);

            //支付平台
            $form->select('platform_id', '支付平台')->options(Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }))->rules('required', ['required' => '必须选择支付平台']);

            //签约主体
            $form->select('entity_id', '签约主体')->options(BusinessEntity::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['company_name']];
            }))->rules('required', ['required' => '必须选择签约主体']);
            $form->textarea('config', '通道参数')->placeholder("支付通道接口参数,如key,秘钥等一行一个:\r\n参数1=值1\r\n参数2=值2..")->rules('required|max:255', ['required' => '必须填写参数', 'max' => '最多255字符']);

            //备用通道
            $spare_channels_filter = [['id', '<>', $form->model()->id]];
            if (!$form->model()->id) {
                //新建通道备用不显示禁用通道
                $spare_channels_filter[] = ['disabled', 0];
            }
            $spare_channels = Channel::where($spare_channels_filter)->get()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            });
            $form->select('spare_channel_id', '备用通道')->rules('required_if:disabled,on', ['required_if' => '必须选择备用通道用于切换'])->options($spare_channels);

            //是否禁用
            $form->switch('disabled', '通道状态')->states([
                'on' => ['value' => 1, 'text' => '禁用', 'color' => 'danger'],
                'off' => ['value' => 0, 'text' => '启用', 'color' => 'success'],
            ]);

            //保存回调
            $form->saving(function (Form $form) {
                $error = '';
                $model_id = $form->model()->getKey();

                //禁用操作限制
                if ($form->disabled == 'on') {
                    if (!$model_id) {
                        //新建表单
                        $spare_channel = Channel::find($form->spare_channel_id);
                    } else {
                        //编辑
                        $spare_channel = $form->model()->spareChannel;
                    }

                    switch (true) {
                        case !$spare_channel:
                            $error = '必须选择备用通道';
                            break;
                        case $spare_channel->disabled:
                            $error = '备用通道被禁用';
                            break;
                        case $model_id && Channel::where([['spare_channel_id', $model_id], ['disabled', 1]])->count() > 0:
                            $error = '被未启用通道备用';
                            break;
                    }

                }

                if ($form->spare_channel_id > 0 && $form->spare_channel_id == $model_id) {
                    $error = '备用通道不能是自身';
                }

                if ($error) {
                    throw new ChannelDisableException($error, !empty($form->input('entity_id')));
                }
            });

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

            $content->header('支付管理');
            $content->description('新增通道');

            $content->body($this->form());
        });
    }
}
