<?php

namespace App\Admin\Controllers;

use App\SystemMessage;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class SystemMessageController extends Controller
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

            $content->header('系统消息');

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

            $content->header('系统消息');

            $content->body($this->form($id)->edit($id)->render());
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

            $content->header('系统消息');

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
        return Admin::grid(SystemMessage::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->title('标题');
            $grid->column('send_at', '发送时间')->display(function($send_at){
                return $send_at ? $send_at : (string)$this->created_at;
            });
            $grid->column("link", '链接')->link();
            $grid->created_at("创建时间");
        });
    }

    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        return Admin::form(SystemMessage::class, function (Form $form) use ($id) {
            $form->display('id', 'ID');
            $form->text('title', '主题');
            $form->datetime('send_at', '发送时间')->help("不填则立即发送");
            $form->url('link', '链接');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
            $form->saved(function (Form $form) use ($id) {
                if (!$id) {
                    if ($form->model()->send_at) {
                        $date = Carbon::parse($form->model()->send_at);
                        \App\Jobs\SystemMessage::dispatch($form->model())->delay($date)->onQueue("messages");

                    } else {
                        \App\Jobs\SystemMessage::dispatch($form->model())->onQueue("messages");
                    }
                }
            });
        });
    }
}
