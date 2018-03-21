<?php

namespace App\Admin\Controllers;

use App\PayInterfaceRecord;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PayInterfaceRecordController extends Controller
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

            $content->header('数据鉴权');
            $content->description('实名认证|银行卡鉴权');

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
        return Admin::grid(PayInterfaceRecord::class, function (Grid $grid) {
            $grid->model()->with('user');
            $grid->bill_id('订单号')->sortable();
            $grid->user_id('用户')->display(function() {
                return <<<EOT
            <div style="width:140px;height:60px;">
                <img style="width:60px;height:60px;margin:auto 10px;border-radius:50%;float:left;" src="{$this->user['avatar']}"/>
                <div style="float:left;width:60px;height:60px;word-break:keep-all; white-space:nowrap; ">{$this->user['name']}<br/>ID:{$this->user['mobile']}</div>
            </div>
EOT;
            });
            $grid->type('类型')->display(function() {
                return $this->get_type_name($this->type );
            });
            $grid->platform('接口平台');
            $grid->request('请求数据')->display(function() {
                $json_decode_data = json_decode($this->request,true)['params'];
                if(is_array($json_decode_data)) {
                    return $json_decode_data;
                } else {
                    return strpos($json_decode_data,'&') ?
                        array_map("urldecode",array_slice(explode('&',$json_decode_data),0,-2)) : $json_decode_data;
                }
            });
            $grid->response('返回数据')->display(function() {
                $json_decode_data = json_decode($this->response,true);
                if(is_array($json_decode_data)) {
                    return $json_decode_data;
                } else {
                    return strpos($this->response,'&') ?
                        explode('&',$this->response):$this->response;
                }
            });
            $grid->status('状态')->display(function(){
                    $class = 'default';
                    switch ($this->status) {
                        case 0:
                        case 2:
                            $class = 'info';
                            break;
                        case 1:
                            $class = 'success';
                            break;
                        case 3:
                            $class = 'danger';
                            break;
                    }

                    return "<span class=\"label label-$class\">{$this->get_status_name($this->status )}</span>";
            });
            $grid->created_at('创建时间');

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
    }


}
