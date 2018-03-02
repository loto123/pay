<div class="container-fluid">

    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{Request::url()}}" pjax-container>
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">会长ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="aid" name="aid" class="form-control" placeholder="请输入会长ID" value="{{$aid}}">
                    </div>
                    <label class="col-sm-1 control-label">公会名称：</label>
                    <div class="col-sm-2">
                        <input type="text" id="shop_name" name="shop_name" class="form-control" placeholder="请输入公会名称" value="{{$shop_name}}">
                    </div>
                    <label class="col-sm-1 control-label">公会ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="shop_id" name="shop_id" class="form-control" placeholder="请输入公会ID" value="{{$shop_id}}">
                    </div>
                    <label class="col-sm-1 control-label">任务ID：</label>
                    <div class="col-sm-2">
                        <input type="text" id="id" name="id" class="form-control" placeholder="请输入任务ID" value="{{$id}}">
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
                <div class="lead">共 <span class="text-yellow">{{$count}}</span> 个任务  ，累计手续费 <span class="text-yellow">{{$amount}}</span> 元</div>
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
                    <th>任务ID</th>
                    <th>公会</th>
                    <th>会长</th>
                    <th>平台手续费</th>
                    <th>公会收入</th>
                    <th>参与人数</th>
                    <th>任务状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($list as $key => $item)
                <tr>
                    <td>{{$item->en_id()}}</td>
                    <td>
                        @if($item->shop)
                         <div class="user-panel clearfix">
                            <div class="pull-left ml7">
                              <p>{{$item->shop->name}}</p>
                              <span>ID:<span class="text-yellow">{{$item->shop->en_id()}}</span></span>
                            </div>
                         </div>
                        @else
                        无
                        @endif
                    </td>
                    @if($item->shop && $item->shop->manager)
                    <td>
                        <div class="user-panel clearfix">
                            <div class="pull-left">
                                <img src="{{$item->shop->manager->avatar}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                                <p>{{$item->shop->manager->name}}</p>
                                <span>ID:<span class="text-yellow">{{$item->shop->manager->mobile}}</span></span>
                            </div>
                        </div>
                    </td>
                    @else
                        <td>无</td>
                    @endif
                    <td>{{$item->fee_amount}}</td>
                    <td>{{$item->tips()->sum('amount')}}</td>
                    <td>{{$item->joiner_count}}</td>
                    @switch($item->status)
                    @case(1)
                    <td>待结算</td>
                    @break
                    @case(2)
                    <td>已平账</td>
                    @break
                    @case(3)
                    <td>已关闭</td>
                    @break
                    @default
                    <td>未知</td>
                    @endswitch
                    <td><a href="{{url("admin/data/transfer/detail/{$item->id}")}}" pjax-container>详情</a></td>
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
        <div>{{ $list->appends(compact('aid', 'date_time', 'shop_id', 'shop_name', 'id'))->links() }}</div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function() {
        $('#reservation').daterangepicker(null, function(start, end, label) {
        });
    });
</script>
