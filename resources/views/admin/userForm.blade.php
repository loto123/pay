<!--查询开始-->
<div class="box box-primary">
    <div class="box-body">
        <form class="form-horizontal" method="get" action="{{ Request::url() }}" pjax-container>
            <div class="form-group">
                <label class="col-sm-1 control-label">ID：</label>
                <div class="col-sm-2">
                    <input type="text" name="user_id" id="user_id" class="form-control" placeholder="请输入用户ID" value="{{ Request::input("user_id") }}">
                </div>
                <label class="col-sm-1 control-label">上级代理ID：</label>
                <div class="col-sm-2">
                    <input type="text" name="parent_id" id="parent_id" class="form-control" placeholder="请输入代理ID" value="{{ Request::input("parent_id") }}">
                </div>
                <label class="col-sm-1 control-label">上级运营ID：</label>
                <div class="col-sm-2">
                    <input type="text" name="operator_id" id="operator_id" class="form-control" placeholder="请输入运营ID" value="{{ Request::input("operator_id") }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label">用户渠道ID：</label>
                <div class="col-sm-2">
                    <input type="text" name="channel_id" id="channel_id" class="form-control" placeholder="" value="{{ Request::input("channel_id") }}">
                </div>
                <label class="col-sm-1 control-label">身份：</label>
                {{--{{$roles}}--}}
                <div class="col-sm-2">
                    <select class="form-control" id="role" name="role">
                        <option value="0">全部</option>
                        @if(!empty($roles = \App\Role::all()))
                            @foreach($roles as $key => $item)
                                <option value="{{$item['id']}}" {{isset($role) && $role == $item['id'] ? 'selected' : ''}}>{{$item['display_name']}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <span class="col-sm-3">
                    <button type="submit" class="btn btn-primary">查询</button>
                    <button type="button" class="btn btn-primary" onclick="data_export()">导出</button>
                </span>
            </div>
        </form>
    </div>
</div>
<!--查询结束-->


<script language="JavaScript">
    function data_export() {
        var user_id = $("#user_id").val();
        var parent_id = $("#parent_id").val();
        var operator_id = $("#operator_id").val();
        var channel_id = $("#channel_id").val();
        var role = $("#role").val();
        var form = $("<form></form>");
        form.attr('style', 'display:none');
        form.attr('method', 'post');
        form.attr('action', '/admin/excel/user');
        var input1 = $('<input />');
        input1.attr('type', 'hidden');
        input1.attr('name', 'user_id');
        input1.val(user_id);
        var input2 = $('<input />');
        input2.attr('type', 'hidden');
        input2.attr('name', 'parent_id');
        input2.val(parent_id);
        var input3 = $('<input />');
        input3.attr('type', 'hidden');
        input3.attr('name', '_token');
        input3.val(LA.token);
        var input4 = $('<input />');
        input4.attr('type', 'hidden');
        input4.attr('name', 'operator_id');
        input4.val(operator_id);
        var input5 = $('<input />');
        input5.attr('type', 'hidden');
        input5.attr('name', 'channel_id');
        input5.val(channel_id);
        var input6 = $('<input />');
        input6.attr('type', 'hidden');
        input6.attr('name', 'role');
        input6.val(role);
        $('body').append(form);
        form.append(input1);
        form.append(input2);
        form.append(input3);
        form.append(input4);
        form.append(input5);
        form.append(input6);
        form.submit();
        form.remove();
    }

    $(document).ready(function () {
        $('#reservation').daterangepicker(null, function (start, end, label) {
        });
    });
</script>

