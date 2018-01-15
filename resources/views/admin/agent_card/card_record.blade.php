<div class="container-fluid">
    <!--查询开始-->

    @if(session('status'))
        <div class="alert alert-info"> {{session('status')}}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="post" action="/admin/agent_card/card_record">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">制卡人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="allocate_id" id="allocate_id" class="form-control" placeholder="请输入操作者ID"
                               value="{{$allocate_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">拨卡人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="operator_id" id="operator_id" class="form-control" placeholder="请输入运营ID"
                               value="{{$operator_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">收卡人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="promoter_id" id="promoter_id" class="form-control" placeholder="请输入推广员ID"
                               value="{{$promoter_id??''}}">
                    </div>

                    <label class="col-sm-1 control-label">卡号：</label>
                    <div class="col-sm-2">
                        <input type="text" name="card_id" id="card_id" class="form-control" placeholder="请输入卡号"
                               value="{{$card_id??''}}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-1 control-label">起止时间：</label>
                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control"
                               value="{{$date_time??''}}"/>
                    </div>
                    <span class="col-sm-2">
                        <button type="submit" class="btn btn-primary">查询</button>
                        <button type="button" class="btn btn-primary" onclick="data_export()">导出</button>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="box">
        <!--店铺统计表格开始-->
        <div class="box-body table-responsive no-padding">
            @if(isset($list) && !$list->isEmpty())
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>制卡人</th>
                        <th>拨卡人</th>
                        <th>卡号</th>
                        <th>收卡人</th>
                        <th>拨卡时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->allocate_bys['avatar']}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->allocate_bys['name']}}</p>
                                        <span>ID:<span>{{$item->allocate_bys['username']}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->operators['avatar']}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->operators['name']}}</p>
                                        <span>ID:<span>{{$item->operators['username']}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>{{$item->card->mix_id()}}</td>
                            <td>
                                @if($promoter = $item->distributions['promoter'])
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$promoter['avatar']}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$promoter['name']}}</p>
                                        <span>ID:<span>{{$promoter['mobile']}}</span></span>
                                    </div>
                                </div>
                                @else
                                 无
                                @endif
                            </td>
                            <td>{{$item->created_at}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        {{--<div>{{ $list->appends(compact('manager_id', 'shop_id', 'shop_name', 'date_time'))->links() }}</div>--}}
        <!--店铺统计表格结束-->
    </div>
</div>

<script language="JavaScript">
    function data_export() {
        var aid = $("#aid").val();
        var shop_id = $("#shop_id").val();
        var shop_name = $("#shop_name").val();
        var date_time = $("#reservation").val();
        var manager_id = $("#manager_id").val();
        var form = $("<form></form>");
        form.attr('style', 'display:none');
        form.attr('method', 'post');
        form.attr('action', '/admin/excel/shop');
        var input1 = $('<input />');
        input1.attr('type', 'hidden');
        input1.attr('name', 'manager_id');
        input1.val(manager_id);
        var input2 = $('<input />');
        input2.attr('type', 'hidden');
        input2.attr('name', 'date_time');
        input2.val(date_time);
        var input3 = $('<input />');
        input3.attr('type', 'hidden');
        input3.attr('name', '_token');
        input3.val(LA.token);
        var input4 = $('<input />');
        input4.attr('type', 'hidden');
        input4.attr('name', 'shop_id');
        input4.val(shop_id);
        var input5 = $('<input />');
        input5.attr('type', 'hidden');
        input5.attr('name', 'shop_name');
        input5.val(shop_name);
        $('body').append(form);
        form.append(input3);
        form.append(input1);
        form.append(input2);
        form.append(input4);
        form.append(input5);
        form.submit();
        form.remove();
    }

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>
