<div class="container-fluid">

    <!--查询开始-->
    <div class="box">
        <div class="box-body">
            <form class="form-horizontal" method="post" action="{{Request::url()}}" pjax-container>
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">代理ID：</label>
                    <div class="col-sm-1">
                        <input type="text" name="aid" class="form-control" placeholder="请输入代理ID" value="{{$aid}}">
                    </div>
                    <label class="col-sm-1 control-label">身份：</label>
                    <div class="col-sm-2">
                        <select class="form-control" id="role" name="role">
                            <option value="0">全部</option>
                            @foreach($roles as $key => $item)
                                <option value="{{$item->id}}" {{isset($role) && $role == $item->id ? 'selected' : ''}}>{{$item->display_name}}</option>
                            @endforeach
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
    <div class="box box-widget widget-user-2">
        <div class="box-header">
            <div class="widget-user-header">
                <div class="widget-user-image">
                    <img class="img-circle" src="{{$operatorInfo->avatar}}">
                </div>
                <h5 class="widget-user-username" style="font-size:20px;">{{$operatorInfo->name}}</h5>
                <h4 class="widget-user-desc">ID: <span>{{$operatorInfo->username}}</span></h4>
                <h4 class="widget-user-desc">运营业绩: <span class="text-yellow">{{$operatorInfo->operator_fee_amount or 0}}</span></h4>
                <h4 class="widget-user-desc">代理总数: <span class="text-yellow">{{$operatorInfo->child_proxy_count or 0}}</span></h4>
                <h4 class="widget-user-desc">用户总数: <span class="text-yellow">{{$operatorInfo->child_user_count or 0}}</span></h4>
                <h4 class="widget-user-desc">推广员总数: <span class="text-yellow">{{$operatorInfo->promoter_count or 0}}</span></h4>
            </div>
        </div>
        <!--表格开始-->
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>排名</th>
                    <th>用户</th>
                    <th>身份</th>
                    <th>上级代理</th>
                    <th>下级用户总数</th>
                    <th>代理业绩</th>
                    <th>代理分润</th>
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
                                    <span>ID:<span class="text-yellow">{{$item->mobile}}</span></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @foreach ($item->roles as $k => $v)
                                <span class="text-yellow">{{$v->display_name}}</span>
                            @endforeach
                        </td>
                        @if($item->proxy)
                            <td>
                                <div class="user-panel clearfix">
                                    <div class="pull-left">
                                        <img src="{{$item->proxy->avatar}}" width="40" height="40"
                                             class="img-circle">
                                    </div>
                                    <div class="pull-left ml7">
                                        <p>{{$item->proxy->name}}</p>
                                        <span>ID:<span class="text-yellow">{{$item->proxy->mobile}}</span></span>
                                    </div>
                                </div>
                            </td>
                        @else
                            <td>无</td>
                        @endif
                        <td>{{$item->child_user_count or 0}}</td>
                        <td>{{$item->proxy_fee_amount or 0}}</td>
                        <td>{{$item->profit_proxy_amount or 0}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!--表格结束-->
    </div>
    <div>{{ $list->appends(compact('operatorId','aid','date_time','role'))->links() }}</div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>
