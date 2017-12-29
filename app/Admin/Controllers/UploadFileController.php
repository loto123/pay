<?php

namespace App\Admin\Controllers;

use App\Admin\Model\UploadFile;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class UploadFileController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        //dump(UploadFile::getFile(10));
        return Admin::content(function (Content $content) {

            $content->header('后台管理');
            $content->description('文件列表');

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
        return Admin::grid(UploadFile::class, function (Grid $grid) {

            $grid->model()->orderBy('id', 'desc');
            $grid->id('文件ID');
            $grid->save_path('文件类型')->display(function ($path) {
                return strtoupper(pathinfo($path, PATHINFO_EXTENSION));
            });
            $grid->description('说明');
            $grid->column('上传人')->display(function () {
                return $this->uploadBy()->name;
            });
            $grid->created_at('上传时间');
            $grid->actions(function ($actions) {
                $actions->disableEdit();
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

            $content->header('后台管理');
            $content->description('上传文件');

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
        return Admin::form(UploadFile::class, function (Form $form) {
            $form->text('description', '文件说明')->rules('between:1,255', ['between' => '1~255个字符']);
            $form->file('save_path', '选择文件')->rules('required')->uniqueName();
            $form->hidden('user_id')->value(Admin::user()->id);
        });
    }
}
