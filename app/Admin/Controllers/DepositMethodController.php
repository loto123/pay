<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Pay\DepositInterface;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\Platform;
use App\Pay\Model\Scene;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class DepositMethodController extends Controller
{
    use ModelForm;

    private static $supportOS = [DepositMethod::OS_ANY => '不限', DepositMethod::OS_ANDROID => '安卓', DepositMethod::OS_IOS => '苹果'];

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('支付管理');
            $content->description('充值方式');

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
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            //$grid->id('ID')->sortable();
            $grid->column('title', '充值方式');
            $grid->column('platform.name', '平台');
            $grid->column('os', '适用系统')->display(function ($os) {
                return self::$supportOS[$os];
            });

            $scene_map = Scene::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            });
            $grid->scene('场景')->display(function ($scenes) use ($scene_map) {
                array_walk(
                    $scenes, function (&$scene) use ($scene_map) {
                    $scene = '<span class="label label-primary">' . $scene_map[$scene] . '</span>';
                });
                return implode('&nbsp;', $scenes);
            });
            $grid->column('memo', '备注')->display(function ($memo) {
                return "<span title=\"$memo\">" . mb_substr($memo, 0, 10) . (mb_strlen($memo) > 10 ? '..' : '') . '</span>';
            });

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

            $content->header('支付管理');
            $content->description('编辑充值方式');

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
            $form->text('show_label', '展示文本')->placeholder('用于充值方式列表显示')->rules('required|max:10', ['required' => '必填项']);
            $form->select('platform_id', '所属平台')->options(Platform::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }))->rules('required', ['required' => '必须选择所属平台']);
            $form->text('impl', '实现路径')->rules('required|max:255', ['required' => '必填项']);
            $form->text('interact_form', '交互形式')->rules('required|max:255', ['required' => '必填项']);
            $form->text('memo', '备注')->rules('nullable');
            $form->decimal('maximum_amount', '最大充值金额(元,0不限)')->default(0)->rules('required|min:0');

            $form->textarea('config', '接口参数')->rules('nullable');
            $form->saving(function (Form $form) {
                if ($form->impl && !is_subclass_of($form->impl, DepositInterface::class)) {
                    throw new \Exception("储值接口 {$form->impl} 未实现");
                }
            });
            //操作系统
            $form->radio('os', '操作系统')->options(self:: $supportOS)->default(DepositMethod::OS_ANY);

            //场景
            $form->checkbox('scene', '适用场景')->options(Scene::all()->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            }));
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

            $content->header('支付管理');
            $content->description('添加充值方式');
            $content->body($this->form());
        });
    }
}
