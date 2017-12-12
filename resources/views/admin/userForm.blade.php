<!--查询开始-->
<div class="box box-primary">
    <div class="box-body">
        <form class="form-horizontal" method="get" action="{{ Request::url() }}" pjax-container>
            <div class="form-group">
                <label class="col-sm-1 control-label">ID：</label>
                <div class="col-sm-2">
                    <input type="text" name="user_id" class="form-control" placeholder="请输入代理ID" value="{{ Request::input("user_id") }}">
                </div>
                <span class="col-sm-3">
                    <button type="submit" class="btn btn-primary">查询</button>
                </span>
            </div>
        </form>
    </div>
</div>
<!--查询结束-->

