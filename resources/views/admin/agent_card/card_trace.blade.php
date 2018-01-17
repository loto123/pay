<div class="container-fluid">
    <!--查询开始-->

    <div class="row">
        <div class="col-md-6">
            <div class="box-body box">
                <div class="lead">流转记录 <span class="text-yellow">{{count($list)??0}}</span> 条</div>
                 <div class="lead">卡号<span class="text-yellow">{{$card_id}}</span></div>
            </div>
        </div>
    </div>

    <div class="box">
        <!--店铺统计表格开始-->
        <div class="box-body table-responsive no-padding">
            @if(isset($list) && !empty($list))
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>时间</th>
                        <th>转卡人</th>
                        <th></th>
                        <th>收卡人</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($list as $key => $item)
                        <tr>
                            <td>{{$item['created_at']??''}}</td>
                            <td>
                                @if($from = $item['from'])
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$from['avatar']}}" width="40" height="40" class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$from['name']}}</p>
                                            <span>ID:<span>{{$from['username']??$from['mobile']}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                            <td>————</td>
                            <td>
                                @if($to = $item['to'])
                                    <div class="user-panel clearfix">
                                        <div class="pull-left">
                                            <img src="{{$to['avatar']}}" width="40" height="40" class="img-circle">
                                        </div>
                                        <div class="pull-left ml7">
                                            <p>{{$to['name']}}</p>
                                            <span>ID:<span>{{$to['username']??$to['mobile']}}</span></span>
                                        </div>
                                    </div>
                                @else
                                    无
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data text-muted text-center" style="font-size:24px;margin-top:20px;">暂无数据</p>
            @endif

        </div>
        <span class="col-sm-8">
             <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
        </span>
    </div>
</div>

