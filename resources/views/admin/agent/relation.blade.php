<div class="container-fluid">
    <div class="box  box-primary">
        <div class="box-body">
            <form class="form-horizontal">
                {{csrf_field()}}
                <div class="form-group">
                    <label class="col-sm-1 control-label">ID：</label>
                    <div class="col-sm-2">
                        <input type="text" name="title" id="player_id" class="form-control" placeholder="请输入ID">
                    </div>
                    <span class="col-sm-3">
                             <button type="button" id="search-button" class="btn btn-primary">查询</button>
                         </span>
                </div>
            </form>
        </div>
    </div>
    <div class="box  box-primary">
        <div class="box-body table-responsive no-padding">
            <div id="result_row">
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var action = 'relation';
    $(document).off("click", '#update_btn');
    $(document).on("click", '#update_btn', function(){
        var _btn = $(this);
        $.ajax({
            url: "/admin/agent/update",
            type: "post",
            dateType: 'json',
            data: {player_id: _btn.data("id"), action: action, _token: "{{ csrf_token() }}"},
            beforeSend: function() {
                _btn.attr("disabled", true);
            },
            success: function(data) {
                _btn.attr("disabled", false);
                if (data.code == 0) {
                    toastr.success(_btn.text()+"成功");
                    search(_btn.data("id"));
                } else {
                    layer.msg(data.msg);
                }
            },
            error: function() {
                layer.msg(_btn.text()+"失败");
                _btn.attr("disabled", false);
            }
        });
    });
    function search(player_id) {
        $.ajax({
            url: "/admin/agent/relation/update",
            type: "post",
            dateType: 'json',
            data: {player_id: player_id,_token: "{{ csrf_token() }}"},
            beforeSend: function() {
                $("#result_row").hide();
            },
            success: function(data) {
                if (data.code == 0) {
                    $("#result_row").html(data.data).show();
                } else {
                    layer.msg(data.msg);
                }
            }
        });
    }
    $('#search-button').unbind("click").click(function(){
        search($("#player_id").val());
    });
    $('#player_id').unbind("keydown").bind('keydown', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            search($("#player_id").val());
        }
    });
</script>
