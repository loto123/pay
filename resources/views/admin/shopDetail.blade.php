    <div class="container-fluid">
        <div class="box box-widget widget-user-2">
            <div class="box-header">
                <div class="widget-user-header">
                    <h4 class="widget-user-desc">ID： <span>{{\App\Shop::encrypt($list->id)}}</span></h4>
                    <h4 class="widget-user-desc">会长：
                        <div class="user-panel clearfix">
                            <div class="pull-left">
                                <img src="{{$list->manager['avatar']}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                                <p>{{$list->manager['name']}}</p>
                                <span>ID:<span class="text-yellow">{{$list->manager['mobile']}}</span></span>
                            </div>
                        </div>
                    </h4>
                    <h4 class="widget-user-desc">公会会员（{{$list->users->count()}}）
                        @if (!empty($list->users->count()) && $list->users->count()>0 )
                        <div class="user-panel clearfix">
                            @foreach($list->users as $_user)
                            <div style="margin-left: 20px;float: left">
                                <div class="pull-left">
                                    <img src="{{$_user->avatar}}" width="40" height="40" class="img-circle">
                                </div>
                                <div class="pull-left ml7">
                                    <p>{{$_user->name}}</p>
                                    <span>ID:<span class="text-yellow">{{$_user->mobile}}</span></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </h4>
                    <h4 class="widget-user-desc">公会任务笔数：<span>{{$list->transfer_cnt}}</span></h4>
                    <h4 class="widget-user-desc">平台手续率：<span>{{config('platform_fee_percent') . '%'}}</span></h4>
                    <h4 class="widget-user-desc">已付平台手续费：<span>{{$list->fee_amount_cnt??0}}</span></h4>
                    <h4 class="widget-user-desc">公会佣金费率：<span>{{$list->fee . '%'}}</span></h4>
                    <h4 class="widget-user-desc">公会默认收益倍率：<span>{{$list->price}}</span></h4>
                    <h4 class="widget-user-desc">总交易额：<span>{{$list->summary??0}}</span></h4>
                    <h4 class="widget-user-desc">公会获得钻石：<span>{{$list->tip_amount_cnt??0}}</span></h4>
                    <h4 class="widget-user-desc">公会剩余钻石：<span>{{$list->container['balance']}}</span></h4>
                    <h4 class="widget-user-desc">公会状态：<span>{{$list->status>0 ? ($list->status==1?'已解散':'已冻结') :'正常'}}</span></h4>
                    <h4 class="widget-user-desc">公会是否开启任务：<span>{{$list->active?'开启':'关闭'}}</span></h4>
                    <h4 class="widget-user-desc">公会创建时间：<span>{{$list->created_at}}</span></h4>
                </div>
                @if(Admin::user()->isRole('administrator'))
                <form class="form-horizontal" method="post" action="/admin/shop/updates">
                    {{csrf_field()}}
                    <input type="hidden" name="shop_id" value={{$list->id}}>
                    <div class="form-group">
                        <div class="col-sm-8">
                            <h4 class="widget-user-desc">
                                <input type="checkbox" name="status" {{($list->status==\App\Shop::STATUS_FREEZE)?'checked':''}} value="1">冻结公会
                            </h4>
                        </div>
                        <span class="col-sm-8">
                            <button type="submit" class="btn btn-primary">确认</button>
                            <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
                        </span>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

