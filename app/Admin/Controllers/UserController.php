<?php

namespace App\Admin\Controllers;

use App\Shop;
use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
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

            if (Request::input('user_id')) {
                $grid->model()->where('id', Request::input('user_id'));
            }

            $grid->id('编号');
            $grid->name('用户');
            $grid->xxx('交易笔数')->sortable();
            $grid->xxx('余额');
            $grid->xxx('收益');
            $grid->xxx('收款');
            $grid->xxx('付款');
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
            $form->display('name','用户名');
            $form->text('mobile','手机号码');
            $form->checkbox('menu.id','解绑微信号')->options(User::where('id','1')->pluck('name','id'));
            $form->select('menu.name','支付方式')->options(['0'=>'xx','1'=>'xxxx']);
            $form->select('openid','')->options(['0'=>'xx','1'=>'xxxx','2'=>'tt','3'=>'tttt']);
            $form->display('created_at','账号创建时间');
            $form->switch('status','冻结');

            $form->saving(function (Form $form) {
                Log::info(['model'=>[
                    'id' => $form->model()->id,
                    'name' => $form->model()->name,
                    'mobile' => $form->model()->mobile,
                    'openid' => $form->model()->openid,
                    'menu.name' => $form->model()->menu->name,
                    'menu.id' => $form->model()->menu->id,
                    'status' => $form->model()->status,
                ]]);
                Log::info(['form'=>[
                    'id' => $form->id,
                    'name' => $form->name,
                    'mobile' => $form->mobile,
                    'openid' => $form->openid,
                    'menu.name' => $form->menu['name'],
                    'menu.id' => $form->menu['id'],
                    'status' => $form->status,
                ]]);
            });
        });
    }

    public function details($id)
    {
        return Admin::content(function (Content $content) use($id) {
            $list = User::find($id);
            $data = compact('list');
            $content->row(view('admin/userDetail',$data));
            $content->header("用户详情");
        });
    }

}
