<?php

namespace App\Admin\Controllers;

use App\Permission;
use App\Role;
use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class RoleController extends Controller
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

            $content->header('角色管理');

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

            $content->header('角色管理');

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

            $content->header('角色管理');
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
        return Admin::grid(Role::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('标识');
            $grid->display_name('名称');
//            $grid->min('返利比例下限');
//            $grid->max("返利比例上限");
            $grid->perms('权限')->pluck('display_name')->display(function($value){
                $html = '';
                $i = 0;
                foreach ($value as $_value) {
                    $html.= '<span class="label label-success" style="margin-bottom: 5px;display: inline-block;margin-right: 5px;">'.$_value.'</span>';
                    $i++;
                    if ($i % 6 == 0) {
                        $html .= "<br>";
                    }

                }
                return $html;
            });

            $grid->created_at('创建时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Role::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '标识');
            $form->text('display_name', '名称');
//            $form->text('min', '返利比例下限');
//            $form->text('max', '返利比例上限');
            $form->listbox('perms', '权限')->options(Permission::all()->pluck('display_name', 'id'));
            $form->display('created_at', '创建时间');
        });
    }
}
