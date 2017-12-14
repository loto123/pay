<div class="container-fluid">

    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{Request::url()}}">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">用户ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="aid" name="aid" class="form-control" placeholder="请输入用户ID"
                               value="{{$aid}}">
                    </div>
                    <label class="col-sm-1 control-label">店铺ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="shop_id" name="shop_id" class="form-control" placeholder="请输入店铺ID"
                               value="{{$shop_id}}">
                    </div>
                    <label class="col-sm-1 control-label">店主ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="owner_id" name="owner_id" class="form-control" placeholder="请输入店主ID"
                               value="{{$owner_id}}">
                    </div>
                    <label class="col-sm-1 control-label">交易ID：</label>

                    <div class="col-sm-2">
                        <input type="text" id="id" name="id" class="form-control" placeholder="请输入交易ID" value="{{$id}}">
                    </div>
                    <label class="col-sm-1 control-label">支付状态：</label>

                    <div class="col-sm-2">
                        <select class="form-control" id="stat" name="stat">
                            <option value="">全部</option>
                            <option value="1" {{isset($stat) && $stat == 1 ? 'selected="selected"' : ''}}>付款</option>
                            <option value="2" {{isset($stat) && $stat == 2 ? 'selected="selected"' : ''}}>收款</option>
                            <option value="3" {{isset($stat) && $stat == 3 ? 'selected="selected"' : ''}}>撤销</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label">起止时间：</label>

                    <div class="col-sm-2">
                        <input type="text" readonly name="date_time" id="reservation" class="form-control"
                               value="{{$date_time}}"/>
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
                <div class="lead">共 <span class="text-yellow">{{$count}}</span> 条支付记录 ，累计收款 <span
                            class="text-yellow">{{$get_amount}}</span> 元， 累计付款 <span
                            class="text-yellow">{{$put_amount}}</span> 元
                </div>
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
                        <th>支付流水号</th>
                        <th>用户</th>
                        <th>交易ID</th>
                        <th>店主</th>
                        <th>店铺</th>
                        <th>平台交易费</th>
                        <th>店铺分成</th>
                        <th>类型</th>
                        <th>金额</th>
                        <th>实收款</th>
                        <th>实付款</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>
                                @if($item->user)
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$item->user->avatar}}" width="40" height="40"
                                                 class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$item->user->name}}</p>
                                            <span>ID:<span class="text-yellow">{{$item->user->id}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>{{$item->transfer_id}}</td>
                            @if($item->shop && $item->shop->user)
                                <td>
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$item->shop->user->avatar}}" width="40" height="40"
                                                 class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$item->shop->user->name}}</p>
                                            <span>ID:<span class="text-yellow">{{$item->shop->user->id}}</span></span>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>无</td>
                            @endif
                            <td>
                                @if($item->shop)
                                    <div class="user-panel clearfix">
                                        <div class="pull-left ml7">
                                            <p>{{$item->shop->name}}</p>
                                            <span>ID:<span class="text-yellow">{{$item->shop->id}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>{{$item->fee_amount}}</td>
                            <td>{{$item->tip ? $item->tip->amount : 0}}</td>
                            @switch($item->status)
                            @case(1)
                            <td>付款</td>
                            @break
                            @case(2)
                            <td>收款</td>
                            @break
                            @default
                            <td>未知</td>
                            @endswitch
                            <td>{{$item->amount}}</td>
                            <td>{{$item->stat == 2 ? $item->real_amount : 0 }}</td>
                            <td>{{$item->stat == 1 ? $item->real_amount : 0 }}</td>
                            <td>{{$item->stat == 3 ? '已撤销' : '正常' }}</td>
                            <td>{{$item->created_at}}</td>
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
        <div>{{ $list->appends(compact('aid', 'date_time', 'shop_id', 'owner_id', 'id', 'stat'))->links() }}</div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>
