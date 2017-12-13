<div class="container-fluid">

    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{$request->url()}}">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">用户ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="aid" name="aid" class="form-control" placeholder="请输入用户ID" value="{{$aid}}">
                    </div>
                    <label class="col-sm-1 control-label">推荐人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="parent" name="parent" class="form-control" placeholder="请输入推荐人ID" value="{{$parent}}">
                    </div>
                    <label class="col-sm-1 control-label">运营ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="operator" name="operator" class="form-control" placeholder="请输入运营ID" value="{{$operator}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label">起止时间：</label>
                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control" value="{{$date_time}}" />
                    </div>
                    <span class="col-sm-3">
                        <button type="submit" class="btn btn-primary">查询</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    <!--查询结束-->
    <div class="row">
        <div class="col-md-6">
            <div class="box-body box">
                <div class="lead">总交易笔数:<span class="text-yellow">{{$transfer_count}}</span></div>
                <div class="lead">总收款金额（元）:<span class="text-yellow">{{$amount}}</span></div>
                <div class="lead">店铺分润（元）含收茶水费 {{$tip_amount}} 元:<span class="text-yellow">{{$shop_amount}}</span></div>
                <div class="lead">代理分润（元）:<span class="text-yellow">{{$proxy_amount}}</span></div>
                <div class="lead">运营收入（元）:<span class="text-yellow">{{$company_amount}}</span></div>
            </div>
        </div>
    </div>
    <!--表格开始-->
    <div class="box">
        <div class="box-body table-responsive no-padding">
            @if($list && $list->count() > 0)
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>排名</th>
                    <th>用户</th>
                    <th>上级代理</th>
                    <th>上级运营</th>
                    <th>收款笔数</th>
                    <th>收款金额</th>
                    <th>店铺分润</th>
                    <th>代理分润</th>
                    <th>运营业绩</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($list as $key => $item)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td>
                         <div class="user-panel clearfix">
                            <div class="pull-left">
                              <img src="{{$item->avatar}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                              <p>{{$item->name}}</p>
                              <span>ID:<span class="text-yellow">{{$item->id}}</span></span>
                            </div>
                         </div>
                    </td>
                    @if($item->proxy)
                    <td>
                        <div class="user-panel clearfix">
                            <div class="pull-left">
                                <img src="{{$item->proxy->avatar}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                                <p>{{$item->proxy->name}}</p>
                                <span>ID:<span class="text-yellow">{{$item->proxy->id}}</span></span>
                            </div>
                        </div>
                    </td>
                    @else
                        <td>无</td>
                    @endif
                    @if($item->operator)
                        <td>
                            <div class="user-panel clearfix">
                                <div class="pull-left">
                                    <img src="{{$item->operator->avatar}}" width="40" height="40" class="img-circle">
                                </div>
                                <div class="pull-left ml7">
                                    <p>{{$item->operator->name}}</p>
                                    <span>ID:<span class="text-yellow">{{$item->operator->id}}</span></span>
                                </div>
                            </div>
                        </td>
                    @else
                        <td>无</td>
                    @endif
                    <td>{{$item->transfer_record()->count()}}</td>
                    <td>{{$item->transfer_record()->sum('amount')}}</td>
                    <td>{{$item->tips()->sum('amount')}}</td>
                    <td>{{$item->output_profit()->sum('proxy_amount')}}</td>
                    <td>{{$item->output_profit()->sum('fee_amount')}}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="noData-container">
                暂无数据
            </div>
            @endif
        </div>
        <!--表格结束-->
        <div>{{ $list->appends(compact('aid', 'date_time', 'operator', 'parent'))->links() }}</div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function() {
        $('#reservation').daterangepicker(null, function(start, end, label) {
        });
    });
</script>
