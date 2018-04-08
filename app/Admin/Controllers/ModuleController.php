<?php

namespace App\Admin\Controllers;

use App\Bank;

use App\IndexModule;
use App\Role;
use App\User;
use App\UserCard;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;

class ModuleController extends Controller
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

            $content->header('模块管理');
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

            $content->header('编辑模块');
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
            $content->description('创建模块');
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
        return Admin::grid(IndexModule::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->module_id('模块ID')->sortable();
            $grid->name('名称');
            $grid->logo('图标')->image();
            $grid->url('链接')->link();
            $grid->roles('身份')->pluck('display_name')->label();
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
        return Admin::form(IndexModule::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->number("module_id", "模块ID");
            $form->text('name',  '名称')->rules('required');
            $form->image('logo', '图标')->uniqueName()->rules('required');
            $form->url("url", '链接')->rules('required');
            $form->select("type", "类型")->options(IndexModule::$types);
            $form->multipleSelect('roles', '角色')->options(Role::all()->pluck('display_name', 'id'));

            $form->display('created_at', '添加时间');
            $form->display('updated_at', '更新时间');
//
//            $form->saving(function (Form $form) {
//            });
        });
    }
}
