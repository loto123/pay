<div class="nav-tabs-custom">
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <div class="box box-primary">
                <div class="box-body">
                    <form class="form-horizontal" method="get" action="{{ admin_url('agent_card/operate') }}" pjax-container>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">ID：</label>
                            <div class="col-sm-2">
                                <input type="text" value="{{$operator_username}}" name="operator_username" id="operator"
                                       class="form-control" placeholder="请输入运营ID">
                            </div>
                            <label class="col-sm-1 control-label">卡类型：</label>
                            <div class="col-sm-2">
                                @if($card_type_list)
                                <select class="form-control" name="card_type" id="card_type">
                                    @foreach($card_type_list as $value)
                                        <option value="{{$value['id']}}"
                                            @if (isset($card_type) && $value['id'] == $card_type)
                                            selected="selected"
                                            @endif
                                        >{{$value['name']}}</option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
                            <span class="col-sm-3">
                                <button type="submit" id="search-button" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            @if (!empty($operators))
                <div id="result_row">
                    <div class="box">
                        <div class="box-body">
                            <div class="auth-container text-center">
                                <div class="text-center">
                                    <img src="{{ $operators->avatar }}" width="100" height="100" class="img-rounded">
                                </div>
                                <ul class="auth-info-list mt-10 mb-10">
                                    <li class="clearfix">
                                        <div>ID:</div>
                                        <div>{{ $operators->username }}</div>
                                    </li>
                                    <li class="clearfix">
                                        <div>昵称:</div>
                                        <div>{{ $operators->name }}</div>
                                    </li>
                                    <li class="clearfix">
                                        <div>身份:</div>
                                        <div>{{ $operators->roles->first()['name']}}</div>
                                    </li>
                                    <li class="clearfix">
                                        <div>现有VIP卡共:</div>
                                        <div>{{ $card_stock_count??0}}</div>
                                    </li>
                                    <li class="clearfix">
                                        <div>当前类型VIP卡共:</div>
                                        <div>{{ $current_card_cnt??0}}</div>
                                    </li>
                                </ul>
                                <div class="divide-prop-box"><label for="divideProp">添加VIP卡数量</label>
                                    <input value="" type="text" class="divide-prop ml-5" id="card_num">
                                </div>
                                <div class="withdraw-wx mt-15 mb-10">
                                    <button class="btn btn-success ml-10" id="affirm_btn">确定</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


<script type="text/javascript">
    @if (isset($_error))
    $(function () {
        layer.msg('{{ $_error }}');
    });
    @endif
    $('#affirm_btn').click(function () {
        var _this = $(this);
        $.ajax({
            url: "{{ admin_url('agent_card/create_agent_card') }}",
            type: "post",
            data: {
                _token: "{{ csrf_token() }}",
                num: $("#card_num").val(),
                card_type:$("#card_type").val(),
                operator_username:"{{$operator_username}}"
            },
            dataType: 'json',
            success: function (data) {
                layer.msg(data.msg);
                if (data.code == 0) {
                    $.pjax.reload("#pjax-container");
                }
            },
            error: function () {
                layer.msg("请求错误");
            }
        });
    })
</script>
