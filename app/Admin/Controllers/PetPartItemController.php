<?php

namespace App\Admin\Controllers;

use App\PetPart;
use App\PetPartItem;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PetPartItemController extends Controller
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

            $content->header('宠物部位素材');

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

            $content->header('宠物部位素材');

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

            $content->header('宠物部位素材');

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
        return Admin::grid(PetPartItem::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name("素材名");

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
        return Admin::form(PetPartItem::class, function (Form $form) {

            $form->text("name", '素材名');
            $form->select("pet_part_id", '部位')->options(PetPart::all()->pluck("name", "id"));
            $form->image("image", '素材')->uniqueName();

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
