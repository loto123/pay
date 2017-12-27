<?php

namespace App\Admin\Controllers;

use App\Bank;
use App\Http\Controllers\Controller;
use App\Pay\Model\Platform;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayPlatformController extends Controller
{
    use ModelForm;

    private $id = false;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('支付管理');
            $content->description('支付平台');

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
        return Admin::grid(Platform::class, function (Grid $grid) {
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->model()->orderBy('id', 'desc');
            $grid->id('ID')->sortable();
            $grid->column('name', '平台')->editable();
            $grid->depositMethods('充值方式')->display(function ($depositMethods) {
                $methods = array_column($depositMethods, 'disabled', 'title');
                array_walk(
                    $methods, function (&$disabled, $title) {
                    $disabled = '<span class="label label-' . ($disabled ? 'danger' : 'success') . '">' . $title . '</span>';
                });
                return implode('&nbsp;', $methods);
            });

            $grid->withdrawMethods('提现方式')->display(function ($withdrawMethods) {
                $mthods = array_column($withdrawMethods, 'disabled', 'title');
                array_walk(
                    $mthods, function (&$disabled, $title) {
                    $disabled = '<span class="label label-' . ($disabled ? 'danger' : 'success') . '">' . $title . '</span>';
                });
                return implode('&nbsp;', $mthods);
            });

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('name', '平台名称');
            });
            $grid->actions(function ($actions) {
                //$actions->disableEdit();
                //$actions->disableDelete();
            });


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
        $this->id = $id;
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $id = $this->id;
        return Admin::form(Platform::class, function (Form $form) use ($id) {
            $form->text('name', '平台名')->rules('between:2,10', ['between' => '填写2~10个字符']);
            $form->setWidth(4, 2);
            $form->disableReset();

            if ($id) {
                $supported = DB::table('pay_banks_support')->where('platform_id', $id)->get()->pluck('inner_code', 'bank_id')->toJson();
                $form->checkbox('banks_support', '支持银行')->options(Bank::all()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['name']];
                }))->addElementClass('bank-check')->stacked();
                $bank_bind_url = route('associate_bank', ['platform' => $id]);
                $token = csrf_token();

                Admin::script(<<<EOT
                $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': '$token',
            },
             async: false
        });
                var supported = $supported;
            $('div.checkbox').each(function(){
                var _check = $(':checkbox',this);
                var checked = supported.hasOwnProperty(_check.val());
                _check.prop('checked', checked);
                var _txt = $('<input type="text" name="inner_code[]" placeholder="请输入内部编码"/>');
                if (checked) {
                    _txt.val(supported[_check.val()]);
                } else {
                    _txt.hide();
                }
                
                $(this).append(_txt);
            });
            $('.bank-check').on('ifToggled', function(event){
                var checked = $(this).prop('checked');
                $(this).closest('.checkbox').find(':text').toggle(checked).select();
            });
            //提交时先保存关联的银行
            $('form.form-horizontal').submit(function(){
                var checked = [];
                var filled = true;
                $('.bank-check:checked').each(function(){
                    var str_inner_code = $(this).closest('.checkbox').find(':text').val();
                    if (str_inner_code == '') {
                        $(this).closest('.checkbox').find(':text').focus();
                        filled = false;
                        return false;
                    }
                    checked.push({bank_id:$(this).val(), platform_id:$id, inner_code: str_inner_code});
                });
                
                if (!filled) {
                    toastr.error('请填写银行内部编码');
                    return false;
                }
               
               var success = false;
                $.post('$bank_bind_url', JSON.stringify(checked),function(json){
                    if (json.status) {
                       success = true;
                    }
                }, 'json');
                if (!success) {
                    toastr.error('操作失败');
                    return false;
                } 
            });
EOT
                );
            }
        });

    }

    //关联银行操作
    public function bankSupport($platform)
    {
        $commit = false;
        DB::beginTransaction();

        do {
            //清除原记录
            if (DB::table(Platform::SUPPORTED_BANKS_TABLE)->where('platform_id', $platform)->delete() === false) {
                Log::error('delete fail');
                break;
            }

            $toInsert = json_decode(file_get_contents('php://input'), true);

            if ($toInsert === false) {
                Log::error('json decode fail');
                break;
            }

            //插入新纪录
            if ($toInsert) {
                if (!DB::table(Platform::SUPPORTED_BANKS_TABLE)->insert($toInsert)) {
                    Log::error('insert fail');
                    break;
                }
            }
            $commit = true;
        } while (false);

        $commit ? DB::commit() : DB::rollBack();
        return ['status' => $commit];
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

            $content->body($this->form());
        });
    }
}
