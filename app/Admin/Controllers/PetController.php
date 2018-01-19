<?php

namespace App\Admin\Controllers;

use App\Pet;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PetController extends Controller
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

            $content->header('宠物');

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

            $content->header('宠物');

            $content->body($this->form()->edit($id)->render());
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

            $content->header('宠物');

            $content->body($this->form()->render());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Pet::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name("宠物名");

            $grid->created_at("创建时间");
            $grid->actions(function ($actions) {

                // 添加操作
                $actions->prepend('<a href="'.admin_url("pets/".$actions->getKey()."/preview").'"><i class="fa fa-image"></i></a>');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Pet::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text("name", '宠物名');
            $form->image("image", '宠物模版')->uniqueName();

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }

    public function preview($id) {
        return Admin::content(function (Content $content) {

            $content->header('宠物');

            $content->body(view("admin.pet.preview"));
        });
    }
}
