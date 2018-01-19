<?php

namespace App\Admin\Controllers;

use App\Pet;
use App\PetPart;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PetPartController extends Controller
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

            $content->header('宠物部位');

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

            $content->header('宠物部位');

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

            $content->header('宠物部位');

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
        return Admin::grid(PetPart::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name("部位名");

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
        return Admin::form(PetPart::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text("name", '部位名');
            $form->select("pet_id", '宠物')->options(Pet::all()->pluck("name", "id"));
            $form->number("x_index", '部位X坐标');
            $form->number("y_index", '部位Y坐标');
            $form->number("z_index", '部位Z坐标');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
