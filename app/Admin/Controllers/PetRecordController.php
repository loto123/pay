<?php

namespace App\Admin\Controllers;

use App\Pet;
use App\PetRecord;
use App\PetType;
use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Storage;

class PetRecordController extends Controller
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

            $content->header('宠物流转记录');

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
        abort(404);
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        abort(404);
    }

    public function destroy() {
        abort(404);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PetRecord::class, function (Grid $grid) {
            $grid->model()->orderBy("id", "DESC");
            $grid->id('ID')->sortable();
            $grid->column("pet.image", '宠物')->image();
            $grid->column("from_user.name", '转出用户');
            $grid->column("to_user.name", '转入用户');
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->column("type", '转入类型')->display(function($type) {
                switch ($type) {
                    case PetRecord::TYPE_TRANSFER:
                        return "交易";
                        break;
                    case PetRecord::TYPE_NEW:
                        return "系统赠送";
                        break;
                    case PetRecord::TYPE_CANCEL:
                        return "退单";
                        break;
                }
            });
            $grid->created_at("创建时间");
        });
    }
}
