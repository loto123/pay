<div class="container-fluid">
    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="post" action="/admin/shop">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">店小二ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="manager_id" class="form-control" placeholder="店小二ID"
                               value="{{$manager_id}}">
                    </div>

                    <label class="col-sm-1 control-label">商户ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_id" class="form-control" placeholder="请输入商户ID"
                               value="{{$shop_id}}">
                    </div>

                    <label class="col-sm-1 control-label">商户名称：</label>
                    <div class="col-sm-2">
                        <input type="text" name="shop_name" class="form-control" placeholder="请输入商户名称"
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
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="box box-primary">
        <!--店铺统计表格开始-->
        <div class="box-footer table-responsive no-padding mt20">
            @if($list && !$list->isEmpty())
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>排名</th>
                        <th>店铺</th>
                        <th>店主</th>
                        <th>店铺会员数</th>
                        <th>店铺会员收费对象</th>
                        <th>已付平台交易费</th>
                        <th>交易笔数</th>
                        <th>平台交易额</th>
                        <th>单笔最高交易额</th>
                        <th>店铺收入</th>
                        <th>店铺余额</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$sort++}}</td>
                            <td>{{$item->name}}<br/>ID：{{$item->id}}</td>
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->manager_id}}" width="40" height="40" class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->manager_name}}</p>
                                        <span>ID:<span>{{$item->manager_id}}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td>{{$item->shop_user_count}}</td>
                            <td>{{($item->type=='1') ? '大卖家':'小卖家' }}</td>
                            <td>{{'xxx'}}</td>
                            <td>{{$item->transfer_cnt}}</td>
                            <td>{{$item->summary?$item->summary:'0'}}</td>
                            <td>{{$item->max_amount?$item->max_amount:'0'}}</td>
                            <td>{{'xxx'}}</td>
                            <td>{{$item->balance}}</td>
                            <td><a href="/admin/shop/detail/{{$item->id}}">详情</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        <div>{{ $list->links() }}</div>
        <!--店铺统计表格结束-->
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>