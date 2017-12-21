<?php

namespace App\Admin\Controllers;

use App\TransferRecord;
use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use function foo\func;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class UserController extends Controller
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

            $content->header('用户管理');
            $content->row(view('admin.userForm'));
            $content->row($this->grid());
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

            $content->header('用户管理');
            $content->description('编辑');

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
            $content->description('description');

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
        return Admin::grid(User::class, function (Grid $grid) {

            $user_table = (new User)->getTable();
            $grid->model()->leftJoin('transfer_record as tfr', 'tfr.user_id', '=', $user_table .'.id')
                ->select($user_table.'.*',
                    DB::raw('SUM( CASE WHEN stat=1 THEN amount ELSE 0 END) AS payment'),
                    DB::raw('SUM( CASE WHEN stat=2 THEN real_amount ELSE 0 END) AS profit'),
                    DB::raw('COUNT(*) AS transfer_count'));
            if (Request::input('user_id')) {
                $grid->model()->where('id', Request::input('user_id'));
            }
            $grid->model()->groupBy($user_table.'.id');

            $grid->id('编号');
            $grid->name('用户');
            $grid->mobile('手机号');
            $grid->transfer_count('交易笔数');
            $grid->balance('余额');
            $grid->column('pure_profit', '收益')->display(function(){
                return number_format($this->profit - $this->payment,2);
            });
            $grid->profit('收款');
            $grid->payment('付款');
            $grid->xxx('上级运营');
            $grid->xxx('上级代理');
            $grid->xxx('支付渠道');
            $grid->xxx('公司账户');
            //在操作按钮组前添加详情按钮
            $grid->actions(function(Grid\Displayers\Actions $action) {
                $action->prepend("<a  href='/admin/user/detail/{$action->getKey()}' ><i class='fa fa-image'></i></a>");
            });
            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableCreation();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(User::class, function (Form $form) {

            $form->display('id', '编号');
            $form->display('name', '用户名');
            $form->text('mobile', '手机号码');
            $form->text('balance', '余额');
            $form->display('xxx', '身份');
//            $form->checkbox('sss', '解绑微信号')->options(User::where('id','1')->pluck('name','id'));
            $form->display('created_at', '账号创建时间');
            $form->switch('status', '冻结');
            $form->saving(function (Form $form) {
                /*
                 * 提交前需要做一些处理：
                 * 1.解绑微信号，需要弹出一个js确认框
                 * 2.验证手机号的唯一性
                 * */
//                Log::info(['model'=>[
//                    'id' => $form->model()->id,
//                    'name' => $form->model()->name,
//                    'mobile' => $form->model()->mobile,
//                    'balance' => $form->model()->balance,
//                    'status' => $form->model()->status,
//                ]]);
//                Log::info(['form'=>[
//                    'id' => $form->id,
//                    'name' => $form->name,
//                    'mobile' => $form->mobile,
//                    'balance' => $form->balance,
//                    'status' => $form->status,
//                ]]);
            });
        });
    }

    public function details($id)
    {
        return Admin::content(function (Content $content) use($id) {
            $list = User::find($id);
            $transfer_record = TransferRecord::where('user_id', $id)
                ->select(DB::raw('SUM( CASE WHEN  stat=1 THEN amount ELSE 0 END) AS payment'),
                    DB::raw('SUM( CASE WHEN  stat=2 THEN real_amount ELSE 0 END) AS profit'),
                    DB::raw('COUNT(*) AS transfer_count'),DB::raw('SUM(fee_amount) AS fee_amount_count'))
                ->first();
            $data = compact('list', 'transfer_record');
            $content->row(view('admin/userDetail', $data));
            $content->header("用户详情");
        });
    }

}
