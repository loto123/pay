<table class="table table-hover">
        <thead>
        <tr>
            <th>用户</th>
            <th>身份</th>
            {{--<th>绑定代理(玩家身份)</th>--}}
            <th>上级代理</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="{{ $user->avatar }}" class="img-circle">
                    </div>
                    <div class="pull-left ml7">
                        <p>{{ $user->name }}</p>
                        <span>id:<span class="text-yellow">{{ $user->mobile }}</span></span>
                    </div>
                </div>
            </td>
            <td>
                @foreach ($user->roles as $_role)
                    <label class="label-success">{{$_role->display_name}}</label>
                @endforeach
            </td>
            {{--<td>--}}
                {{--@if ($agent)--}}
                    {{--<div class="user-panel">--}}
                        {{--<div class="pull-left image">--}}
                            {{--<img src="{{ $agent->avatar }}" class="img-circle">--}}
                        {{--</div>--}}
                        {{--<div class="pull-left ml7">--}}
                            {{--<p>{{$agent->user_info ? $agent->user_info->unick : $agent->username}}</p>--}}
                            {{--<span>id:<span--}}
                                        {{--class="text-yellow">{{$agent->user_info ? $agent->user_info->aid : $agent->id}}</span></span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--@endif--}}
            {{--</td>--}}
            <td>
                @if ($user->parent)
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{ $user->parent->avatar }}" class="img-circle">
                        </div>
                        <div class="pull-left ml7">
                            <p>{{$user->parent->name}}</p>
                            <span>id:<span class="text-yellow">{{$user->parent->mobile}}</span></span>
                        </div>
                    </div>
                @endif
            </td>
        </tr>
        </tbody>
    </table>