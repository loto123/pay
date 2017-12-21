<div class="container-fluid">

    <!--查询开始-->
    <div class="box box-primary">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{Request::url()}}" pjax-container>
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">运营ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="aid" class="form-control" placeholder="请输入运营ID" value="{{$aid}}">
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
    <!--表格开始-->
    <div class="box">
        <div class="box-body table-responsive no-padding">
            @if($list && $list->count() > 0)
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>排名</th>
                    <th>运营</th>
                    <th>运营业绩</th>
                    <th>用户总数</th>
                    <th>推广员数</th>
                    <th>代理数</th>
                    <th>操作</th>
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
                              <span>ID:<span class="text-yellow">{{$item->username}}</span></span>
                            </div>
                         </div>
                    </td>
                    <td>{{$item->operator_fee_amount or 0}}</td>
                    <td>{{$item->child_user_count or 0}}</td>
                    <td>{{$item->promoter_count or 0}}</td>
                    <td>{{$item->child_proxy_count or 0}}</td>
                    <td><a href="/admin/data/areaDetail/{{$item->id}}">详情</a></td>
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
        <div>{{ $list->appends(compact('aid','date_time'))->links() }}</div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function() {
        $('#reservation').daterangepicker(null, function(start, end, label) {
        });
    });
</script>
