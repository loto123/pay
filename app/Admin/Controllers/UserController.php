<?php

namespace App\Admin\Controllers;

use App\OauthUser;
use App\Pay\Model\Channel;
use App\Pay\Model\PayFactory;
use App\Role;
use App\TransferRecord;
use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\MessageBag;

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
            $parent_id = Request::input('parent_id');
            $operator_id = Request::input('operator_id');
            $channel_id = Request::input('channel_id');
            $role = Request::input('role');
            $data = compact('parent_id','operator_id','channel_id','role');
            $content->header('用户管理');
            $content->row(view('admin.userForm',$data));
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
            $list = User::where('id',$id)->with(['roles','wechat_user'])->first();
            $data = compact('list');
            $content->header('用户管理');
            $content->description('编辑');

            $content->body($this->form($id)->edit($id));
//            $content->body(view('admin.userEdit',$data));
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

            $user_id = Request::input('user_id');
            $parent_id = Request::input('parent_id');
            $operator_id = Request::input('operator_id');
            $channel_id = Request::input('channel_id');
            $role = Request::input('role');

            $user_table = (new User)->getTable();
            $grid->model()->leftJoin('transfer_record as tfr', 'tfr.user_id', '=', $user_table .'.id')
                ->with(['roles', 'operator'])
                ->select($user_table.'.*',
                    DB::raw('abs(SUM( CASE WHEN stat=1 THEN amount ELSE 0 END)) AS payment'),
                    DB::raw('abs(SUM( CASE WHEN stat=2 THEN real_amount ELSE 0 END)) AS profit'),
                    DB::raw('COUNT(tfr.id) AS transfer_count'));
            if ($user_id) {
                $grid->model()->where($user_table.'.id', $user_id);
            }
            if ($parent_id) {
                $grid->model()->where($user_table.'.parent_id', $parent_id);
            }
            if ($operator_id) {
                $grid->model()->where($user_table.'.operator_id', $operator_id);
            }
            if ($role > 0) {
                $grid->model()->whereHas('roles', function ($query) use ($role) {
                    $query->where('id', $role);
                });
            }
            if ($channel_id) {
                $grid->model()->where($user_table.'.channel_id', $channel_id);
            }
            $grid->model()->groupBy($user_table.'.id')->orderBy('transfer_count','DESC')->orderBy($user_table.'.id');

            $grid->id('编号');
            $grid->avatar('用户头像')->image('',70,70);
            $grid->column('user','用户')->display(function () {
                return "<span style='color:black'>$this->name</span><br/><span style='color:gray'>$this->mobile</span>";
            });
            $grid->roles('身份')->pluck('display_name')->label();
            $grid->transfer_count('交易笔数');
            $grid->column('container.balance','余额');
            $grid->column('pure_profit', '收益')->display(function(){
                return number_format($this->profit - $this->payment,2);
            });
            $grid->profit('收款')->display(function () {
                return number_format($this->profit,2);
            });
            $grid->payment('付款')->display(function () {
                return number_format($this->payment,2);
            });
            $grid->column('operators','上级运营')->display(function ($value) {
                if ($this->operator){
                    return $this->operator['username'] . ' ' .$this->operator['name'];
                }else {
                    return '无';
                }
            });
            $grid->parent_id('上级代理')->display(function () {
                if($this->parent_id>0) {
                    return $this->parent_id;
                } else{
                    return '无';
                }
            });
            $grid->channel_id('支付渠道');
            //判断角色
            $grid->actions(function (Grid\Displayers\Actions $action) use ($grid) {
                if(!Admin::user()->isRole('administrator')) {
                    $action->disableDelete();
                } else if( User::where('parent_id',$action->getKey())->count() >0) {
                    $action->disableDelete();
                }
                //在操作按钮组前添加详情按钮
                $action->prepend("<a  href='/admin/user/detail/{$action->getKey()}' ><i class='fa fa-image'></i></a>");
            });
            $grid->disableFilter();
            $grid->disableExport();
//            $grid->disableCreation();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id='')
    {
        return Admin::form(User::class, function (Form $form) use($id){

            $form->display('id', '编号');
            if ($id) {
                $form->display('name', '用户名');
            } else {
                $form->text('name', '用户名');
            }
            $form->password('password', '密码');
            $form->text('mobile', '手机号码');
//            $form->display('name', '身份角色');
            if ($id) {
                $form->text('container.balance', '余额');
            }
            $form->multipleSelect('roles', '角色')->options(Role::all()->pluck('display_name', 'id'));
            $form->select('operator_id','上级运营')->options(\App\Admin::whereHas("roles", function($query){
                $query->where("slug", 'operator');
            })->pluck("username", 'id'));
            if ($id) {
                $form->checkbox('wechat', '解绑微信号')->options(OauthUser::where('user_id',$id)->pluck('nickname','id'));
            }
            $form->display('created_at', '账号创建时间');
            $form->switch('status', '冻结');
            $form->hidden('container_id');
            $form->hidden('channel_id');
            $form->ignore(['wechat']);
            $form->saving(function (Form $form) {
                /*
                 * 提交前需要做一些处理：
                 * 1.解绑微信号，需要弹出一个js确认框
                 * 2.验证手机号的唯一性
                 * */
                if($form->model()->mobile != $form->mobile && User::where('mobile',$form->mobile)->count()>0) {
                    $error = new MessageBag([
                        'title'   => '操作有误',
                        'message' => '您修改的手机号已经存在',
                    ]);
                    return back()->with(compact('error'));
                }
                $wechat = Request::input('wechat');
                if (!empty($wechat) && count($wechat)>0) {
                    array_pop($wechat);
                    if (!empty($wechat)){
                        OauthUser::whereIn('id',$wechat)->update(['user_id'=>'0']);
                    }
                }
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                } else {
                    $form->password = $form->model()->password;
                }
                if (!$form->container_id) {
                    $wallet = PayFactory::MasterContainer();
                    $wallet->save();
                    $form->container_id = $wallet->id;
                }
                if (!$form->channel_id) {
                    $channel = Channel::where("disabled",0)->inRandomOrder()->first();
                    $form->channel_id = $channel->id;
                }
            });

        });
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if ($user->child_proxy()->count() > 0) {
            abort(404);
        }
        if ($this->form()->destroy($id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户管理');
            $content->description('description');

            $content->body($this->form());
        });
    }


    public function details($id)
    {
        return Admin::content(function (Content $content) use($id) {
            $list = User::where('id',$id)->with(['roles','wechat_user'])->first();
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
