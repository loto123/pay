<?php

namespace App\Admin\Controllers;

use App\Pet;
use App\PetType;
use App\User;
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
            $grid->model()->with(["user",'pet_type']);
            $grid->id('ID')->sortable();
            $grid->column("user.name", '用户');

            $grid->created_at("创建时间");
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
            $form->select("user_id", '用户')->options(User::all()->pluck("name", 'id'));
//            $form->image("image", '宠物模版')->uniqueName();

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
