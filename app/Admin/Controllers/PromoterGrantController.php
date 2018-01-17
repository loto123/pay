<?php

namespace App\Admin\Controllers;

use App\Agent\PromoterGrant;
use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class PromoterGrantController extends Controller
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

            $content->header('推广员');
            $content->description('授权列表');

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
        return Admin::grid(PromoterGrant::class, function (Grid $grid) {
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $grid->grantBy('授权人')->display(function () {
                $c = $this->by_admin ? 'success' : 'info';
                $role = $this->by_admin ? '后台' : '推广员';

                Log::debug('推广员授权记录', ['model' => PromoterGrant::find($this->getKey()), 'grantBy' => $this->grantBy]);
                $display = $this->by_admin ? $this->grantBy->name : $this->grantBy->mobile;
                return "<span class=\"label label-$c\">$role</span>&nbsp;" . $display;
            });
            $grid->column('grantTo.name', '授权给');
            $grid->column('grantTo.mobile', '被授权ID');
            $grid->created_at('授权时间');
            $grid->confirmed_at('确认时间');
            $grid->grant_result('授权结果')->display(function ($value) {
                return '<span class="label label-' . [PromoterGrant::CONFIRM_PENDING => 'default">未确认', PromoterGrant::CONFIRM_ACCEPT => 'success">已接受', PromoterGrant::CONFIRM_DENY => 'danger">已拒绝'][$value] . '</span>';
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

            $content->header('推广员');
            $content->description('新增授权');

            $content->body($this->form());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(PromoterGrant::class, function (Form $form) {
            $form->text('grant_to', '用户id')->placeholder('输入11位数手机号码')->rules('digits:11', ['digits' => '请输入有效的手机号码']);
            $form->hidden('grant_by')->value(Admin::user()->id);
            $form->hidden('by_admin')->value(1);
            $form->saving(function (Form $form) {
                $user = User::where('mobile', $form->grant_to)->first();
                if (!$user) {
                    throw new Exception('用户不存在');
                }

                /**
                 * @var $user User
                 */
                if ($user->isPromoter()) {
                    throw new Exception('该用户已经是推广员');
                }
                $form->input('grant_to', $user->getKey());
            });
        });
    }
}
