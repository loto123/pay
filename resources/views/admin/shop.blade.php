<div class="container-fluid">
    <!--查询开始-->

    @if(session('status'))
        <div class="alert alert-info"> {{session('status')}}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="post" action="/admin/shop">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">会长ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="manager_id" id="manager_id" class="form-control" placeholder="店小二ID"
                               value="{{$manager_id}}">
                    </div>

                    <label class="col-sm-1 control-label">公会ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_id" id="shop_id" class="form-control" placeholder="请输入公会ID"
                               value="{{$shop_id}}">
                    </div>

                    <label class="col-sm-1 control-label">公会名称：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_name" id="shop_name" class="form-control" placeholder="请输入公会名称"
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
        <!--公会统计表格开始-->
        <div class="box-body table-responsive no-padding">
            @if($list && !$list->isEmpty())
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>排名</th>
                        <th>公会</th>
                        <th>会长</th>
                        <th>公会会员数</th>
                        <th>公会佣金费率</th>
                        <th>已付平台手续费</th>
                        <th>任务笔数</th>
                        <th>总交易额</th>
                        <th>公会获得钻石</th>
                        <th>公会剩余钻石</th>
                        <th>公会状态</th>
                        <th>公会任务开关</th>
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
                                        <span>ID:<span>{{$item->manager['mobile']}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>{{$item->shop_user_count}}</td>
                            <td>{{$item->fee . '%'}}</td>
                            <td>{{$item->fee_amount_cnt??0}}</td>
                            <td>{{$item->transfer_cnt}}</td>
                            <td>{{$item->summary??0}}</td>
                            <td>{{$item->tip_amount_cnt??0}}</td>
                            <td>{{$item->container['balance']}}</td>
                            <td>
                                @if($item->status == 0)
                                    <span style='color:green'>正常</span>
                                    @elseif($item->status == 1)
                                    <span style='color:red'>已解散</span>
                                    @else
                                    <span style='color:blue'>已冻结</span>
                                @endif
                            </td>
                            <td>
                                @if($item->active==1)
                                <span style='color:green'>开启</span>
                                @else
                                <span style='color:red'>关闭</span>
                                @endif
                            </td>
                            <td><a href="/admin/shop/detail/{{$item->id}}">详情</a></td>
                            @if(Admin::user()->isRole('administrator'))
                                @if($item->status == \App\Shop::STATUS_NORMAL)
                                    <td><a href="/admin/shop/updates/{{$item->id}}/{{\App\Shop::STATUS_FREEZE}}"><span style='color:green'>冻结</span></a></td>
                                @elseif ($item->status == \App\Shop::STATUS_FREEZE)
                                    <td><a href="/admin/shop/updates/{{$item->id}}/{{\App\Shop::STATUS_NORMAL}}"><span style='color:blue'>解冻</span></a></td>
                                @endif
                            @endif
                            @if(Admin::user()->isRole('administrator') && $unclose_cnt_list[$item->id] == 0)
                                <td><a href="/admin/shop/delete/{{$item->id}}"><span style='color:red'>删除</span></a></td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        <div>{{ $list->appends(compact('manager_id', 'shop_id', 'shop_name', 'date_time'))->links() }}</div>
        <!--公会统计表格结束-->
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
