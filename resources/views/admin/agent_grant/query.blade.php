<div class="nav-tabs-custom">
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <div class="box box-primary">
                <div class="box-body">
                    <form class="form-horizontal" method="get" action="{{ admin_url('agent_grant/query') }}" pjax-container>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">用户ID：</label>
                            <div class="col-sm-2">
                                <input type="text" value="{{$player??''}}" name="player" id="player"
                                       class="form-control" placeholder="请输入用户ID">
                            </div>
                            <span class="col-sm-3">
                                <button type="submit" id="search-button" class="btn btn-primary">查询</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            @if (!empty($user))
                <div id="result_row">
                    <div class="box">
                        <div class="box-body">
                            <div class="auth-container text-center">
                                <div class="text-center">
                                    <img src="{{ $user->avatar }}" width="100" height="100" class="img-rounded">
                                </div>
                                <div>
                                    <ul class="auth-info-list mt-10 mb-10">
                                        <li class="clearfix">
                                            <div>ID:</div>
                                            <div>{{ $user->mobile }}</div>
                                        </li>
                                        <li class="clearfix">
                                            <div>昵称:</div>
                                            <div>{{ $user->name }}</div>
                                        </li>
                                        <li class="clearfix">
                                            <div>身份:</div>
                                            <div>
                                                @if(!empty($user->roles))
                                                    @foreach($user->roles as $_role)
                                                    {{ $_role->display_name}}
                                                    @endforeach
                                                @endif
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <label class="col-sm-1 control-label">授权为：</label>
                                <div class="col-sm-2">
                                    @if($roles = \App\AgentGrant::getUserAllowGrantRoles($user))
                                        <select class="form-control" name="role" id="role">
                                            @foreach($user_grant_roles as $value)
                                                <option value="{{$value['id']}}"
                                                        @if (isset($user_selected_role) && $value['id'] == $user_selected_role)
                                                        selected="selected"
                                                        @endif
                                                >{{$value['display_name']}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="withdraw-wx mt-15 mb-10">
                                    <button class="btn btn-success ml-10" id="affirm_btn">确认授权</button>
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
            url: "{{ admin_url('agent_grant/grant') }}",
            type: "post",
            data: {
                _token: "{{ csrf_token() }}",
                role: $("#role").val(),
                player:"{{$player??''}}",
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
