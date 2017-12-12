    <div class="container-fluid">
        <div class="box box-widget widget-user-2">
            <div class="box-header">
                <div class="widget-user-header">
                    <h4 class="widget-user-desc">ID： <span>{{$list->id}}</span></h4>
                    <h4 class="widget-user-desc">店主：
                        <div class="user-panel clearfix">
                            <div class="pull-left">
                                <img src="{{''}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                                <p>{{$list->manager_name}}</p>
                                <span>ID:<span class="text-yellow">{{$list->manager_id}}</span></span>
                            </div>
                        </div>
                    </h4>
                    <h4 class="widget-user-desc">店铺会员（{{3}}）：
                        @if (!empty($users_arr) && $users_arr->count()>0 )
                        <div class="user-panel clearfix">
                            @foreach($users_arr as $user_item)
                            <div style="margin-left: 20px;float: left">
                                <div class="pull-left">
                                    <img src="{{''}}" width="40" height="40" class="img-circle">
                                </div>
                                <div class="pull-left ml7">
                                    <p>{{$user_item->name}}</p>
                                    <span>ID:<span class="text-yellow">{{$user_item->id}}</span></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </h4>
                    <h4 class="widget-user-desc">店铺交易笔数：<span>{{$list->transfer_cnt}}</span></h4>
                    <h4 class="widget-user-desc">平台交易费率：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">已付平台交易费：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">店铺会员收费对象：<span>{{($list->type=='1') ? '大卖家':'小卖家' }}</span></h4>
                    <h4 class="widget-user-desc">店铺分成比例：<span>{{$list->percent}}</span></h4>
                    <h4 class="widget-user-desc">店铺默认茶水费：<span>{{$list->fee}}</span></h4>
                    <h4 class="widget-user-desc">店铺收入：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">平均交易额：<span>{{($list->transfer_cnt>0)?round($list->summary/$list->transfer_cnt,2):0}}</span></h4>
                    <h4 class="widget-user-desc">单笔最高交易额：<span>{{$list->max_amount?$list->max_amount:'0'}}</span></h4>
                    <h4 class="widget-user-desc">店铺创建时间：<span>{{$list->created_at}}</span></h4>
                </div>
                <form class="form-horizontal" method="post" action="/admin/shop/updates">
                    {{csrf_field()}}
                    <input type="hidden" name="shop_id" value={{$list->id}}>
                    <div class="form-group">
                        <div class="col-sm-8">
                            <h4 class="widget-user-desc">
                                <input type="checkbox" name="status" {{($list->status==2)?'checked':''}} value="2">冻结店铺
                            </h4>
                        </div>
                        <span class="col-sm-8">
                            <button type="submit" class="btn btn-primary">确认</button>
                            <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

