<div class="container-fluid">
    <!--查询开始-->

    <div class="box box-primary">
        <div class="box-header">
            <form class="form-horizontal" method="get" action="/admin/agent_grant/records">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">被授权人ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="player" id="player" class="form-control" placeholder="请输入ID"
                               value="{{$player??''}}">
                    </div>
                    <span class="col-sm-2">
                        <button type="submit" class="btn btn-primary">查询</button>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="box">
        <!--店铺统计表格开始-->
        <div class="box-body table-responsive no-padding">
            @if($records)
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>授权人ID</th>
                        <th>授权人昵称</th>
                        <th>授权人身份</th>
                        <th>被授权人ID</th>
                        <th>被授权人昵称</th>
                        <th>授权前身份</th>
                        <th>授权后身份</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($records as $key => $item)
                        <tr>
                            <td>{{$item->by_admin?$item->agentGrantBy['username']:$item->agentGrantBy['mobile']}}</td>
                            <td>{{$item->agentGrantBy['name']}}</td>
                            <td>
                                @if($roles = $item->agentGrantBy['roles'])
                                    @if ($item->by_admin)
                                        @foreach($roles as $_role)
                                        {{$_role->name.'、'}}
                                        @endforeach
                                    @else
                                        @foreach($roles as $_role)
                                            {{$_role->display_name}}}
                                        @endforeach
                                     @endif
                                @endif
                            </td>
                            <td>{{$item->agentGrantTo['mobile']}}</td>
                            <td>{{$item->agentGrantTo['name']}}</td>
                            <td>
                                {{$item->old_roles?$item->get_roles($item->old_roles):''}}
                            </td>
                            <td>{{$item->new_roles?$item->get_roles($item->new_roles):''}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif
        </div>
        <div>{{ $records->appends(compact('player'))->links() }}</div>
    </div>
</div>

