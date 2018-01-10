<?php

namespace App\Admin\Controllers;

use App\Version;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class VersionController extends Controller
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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(Version::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->column('platform', '平台')->display(function($platform){
                return $platform == Version::PLATFORM_ANDROID ? "Android" : "iOS";
            });
            $grid->column('ver_name', '版本名');
            $grid->column('ver_code', '版本号');
            $grid->column('url', '下载地址')->display(function ($url) {
                return "<a target='_blank' href='".$url."'>".$url."</a>";
            });
            $grid->column('changelog', '更新日志');
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->equal('platform', '平台')->select([Version::PLATFORM_IOS => 'iOS',Version::PLATFORM_ANDROID => 'Android']);
            });
            $grid->created_at("添加时间");
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Version::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->select('platform','平台')->options([0=>'iOS', 1=>'Android'])->rules('required');
            $form->text('ver_name', '版本名')->help("a.b.c形式。a为大版本号,大版本号变化则强制非该大版本号的客户端强制更新。b为功能版本号,增加了新功能。c为修复版本号,修复了问题")->rules('required');
            $form->number('ver_code', '版本号')->help("数字形式，小于该版本号的客户端会被提示更新")->rules('required')->default(1);
            $form->file('url_file', '文件包');
            $form->text('url_link', '文件下载链接');
            $form->hidden("url");
            $form->hidden("md5");

            $form->textarea('changelog', '更新日志')->rules('required');


            $form->display('created_at', '添加时间');
            $form->display('updated_at', '更新时间');
            $form->ignore(['url_file', 'url_link']);
            $form->saving(function (Form $form) {


                foreach (['ver_name', 'ver_code'] as $_key) {
                    if ($form->model()->$_key != $form->$_key) {
                        $exist = Version::where('platform', $form->platform)->where($_key, $form->$_key)->first();
                        if ($exist && $exist->id != $form->model()->id) {
                            return back()->withErrors([$_key => '已存在相同版本']);
                        }
                    }
                }
                if (Request::hasFile("url_file")) {
//                    $file = Storage::disk(config('admin.upload.disk'))->get(request()->url_file);
//                    if ($file) {
//                        $form->md5 = md5($file);
//                    }
                    $file = Request::file("url_file");
                    $form->md5 = md5_file($file->path());
                    $form->url = Storage::disk(config('admin.upload.disk'))->putFileAs(config("admin.upload.directory.file"), $file, md5(uniqid()).'.'.$file->getClientOriginalExtension());
                } else if (Request::input("url_link")) {
                    $form->url = Request::input("url_link");
                    $form->md5 = md5(Request::input("url_link"));
                }

            });
        });
    }
}
