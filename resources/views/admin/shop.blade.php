<div class="container-fluid">
    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="post" action="/admin/shop">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">店主ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="manager_id" id="manager_id" class="form-control" placeholder="店小二ID"
                               value="{{$manager_id}}">
                    </div>

                    <label class="col-sm-1 control-label">商户ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_id" id="shop_id" class="form-control" placeholder="请输入商户ID"
                               value="{{$shop_id}}">
                    </div>

                    <label class="col-sm-1 control-label">商户名称：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_name" id="shop_name" class="form-control" placeholder="请输入商户名称"
                               value="{{$shop_name}}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-1 control-label">起止时间：</label>
                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control"
                               value="{{$date_time}}"/>
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
            @if($list && !$list->isEmpty())
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>排名</th>
                        <th>店铺</th>
                        <th>店主</th>
                        <th>店铺会员数</th>
                        <th>店铺手续费率</th>
                        <th>已付平台交易费</th>
                        <th>交易笔数</th>
                        <th>总交易额</th>
                        <th>店铺收入</th>
                        <th>店铺余额</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$key+$offset+1}}</td>
                            <td>{{$item->name}}<br/>ID：{{$item->en_id()}}</td>
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->manager['avatar']}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->manager['name']}}</p>
                                        <span>ID:<span>{{\App\User::encrypt($item->manager_id)}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>{{$item->shop_user_count}}</td>
                            <td>{{(int)$item->type_value . '%'}}</td>
                            <td>{{$item->fee_amount_cnt??0}}</td>
                            <td>{{$item->transfer_cnt}}</td>
                            <td>{{$item->summary??0}}</td>
                            <td>{{$item->tip_amount_cnt??0}}</td>
                            <td>{{$item->container['balance']}}</td>
                            <td><a href="/admin/shop/detail/{{$item->id}}">详情</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        <div>{{ $list->appends(compact('manager_id', 'shop_id', 'shop_name', 'date_time'))->links() }}</div>
        <!--店铺统计表格结束-->
    </div>
</div>

<script language="JavaScript">
    function data_export() {
        var aid = $("#aid").val();
        var shop_id = $("#shop_id").val();
        var shop_name = $("#shop_name").val();
        var date_time = $("#reservation").val();
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
        form.append(input1);
        form.append(input2);
        form.append(input3);
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
