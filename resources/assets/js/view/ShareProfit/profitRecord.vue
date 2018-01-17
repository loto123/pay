<template>
    <div id="profit-record">
        <topBack title="账单明细"
            style="background:#26a2ff;color:#fff;"
        >
            <div class="flex flex-reverse flex-align-center header-right">
                <!-- <a href="javascript:;" @click="show">筛选</a> -->
                <i class="iconfont" style="font-size:1.4em;" @click="filterDate">
                    &#xe704;
                </i>
            </div>
        </topBack>

        <div class="change-tab flex">
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(0)" :class="{active:tabStatus[0]}">收益明细</div>
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(1)" :class="{active:tabStatus[1]}">提现记录</div>
        </div>

        <div class="tab-fixed flex flex-v flex-align-start" v-if="recordList.length != 0">
            <div class="month">{{timeInfo}}</div>
            <div class="amount">{{tabStatus[0]==true?'收益：':'提现：'}}{{tabTotal}}</div>
        </div>

        <div class="bill-box"  >
            <div class="bill-date flex flex-align-center flex-justify-between" style="display:none;">
                <div class="left-content">
                    <div class="cur-date">2017年11月</div>
                    <div class="month-money flex">
                        <div class="expend flex-1">支出¥
                            <span>616.55</span>
                        </div>
                        <div class="income">收入¥
                            <span>616.55</span>
                        </div>
                    </div>
                </div>
                <div>图标</div>
            </div>

            <div v-if="recordList.length == 0" class="flex flex-v flex-align-center nodata" >
                <i class="iconfont">
                    &#xe655;
                </i>
                <div>暂无数据</div>
            </div>

            <ul class="bill-list" v-else v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80" >

                <li  v-for="item in recordList" :class="{'time-tab':item.isTimePanel}">
                    <a href="javascript:;" class="flex" v-if="item.isTimePanel == false" @click="details(item.id)">
                        <div class="bill-content">
                            <h5>{{tabStatus[0]?"分润":"提现"}}(分润比例 {{item.proxy_percent}})</h5>
                            <div class="time">{{item.created_at}}</div>
                        </div>
                        <div class="bill-money" v-bind:class="[item.mode == 1?'':'active']">{{item.proxy_amount}}</div>
                    </a>

                    <div v-if="item.isTimePanel == true" class="time-tab" ref="timeTab">
                        <div class="month">{{item.time}}</div>
                        <div class="amount">{{tabStatus[0]==true?'收益：':'提现：'}}{{item.total}}</div>
                    </div>
                </li>
            </ul>

            <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
                <!--<span>-->
                <mt-spinner type="fading-circle"></mt-spinner>
                <span style="margin-left: 0.5em;color:#999;">加载中...</span>
                <!--</span>-->
            </p>
        </div>
       
        <mt-datetime-picker
            v-model="dateModel"
            class="profit-date"
            type="date"
            ref="picker"
            year-format="{value} 年"
            month-format="{value} 月"
            :startDate="startDate"
            :endDate="endDate"
            @confirm="choiseDate">
        </mt-datetime-picker>

    </div>
</template>

<script>
    import request from '../../utils/userRequest';
    import topBack from "../../components/topBack.vue";
    import Loading from '../../utils/loading'
    import { Toast } from "mint-ui";
    import moment from "moment"

    export default {
        data() {
            return {
                showAlert: false,
                type:null,              //类型
                created_at:null,        //结束时间
                size:null,              //数目
                recordList:[],

                headList:[],            // timeTab数组
                timeInfo:"",
                tabTotal:"",
                tabStatus:[true,false],

                wrapperHeight:null,
                loading: false,
                allLoaded: false,
                canLoading:true,

                dateModel:null,
                dateChoise:null,    // 选择的日期
                startDate:new Date("2017,1,1"),
                endDate:new Date()
            };
        },
        created(){
            this.init();
        },

        mounted(){
            // this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
            window.addEventListener('scroll', this.handleScroll);
        },

        methods: {
            show() {
                this.showAlert = true;
            },
            cancel() {
                this.showAlert = false;
            },
            details(id) {
                if(this.tabStatus[0] == true){
                    // 分润状态
                    this.$router.push({ path: "/profit_record/detail/?id="+id+"&type=profit"});
                }else if(this.tabStatus[1] == true){
                    // 提现状态
                    this.$router.push({ path: "/profit_record/detail/?id="+id+"&type=withDraw"});
                }
            },

            init(){

                Loading.getInstance().open();
                this.dateChoise = null;
                var _data = {
                    limit:15,
                    offset:0
                }
                
                Loading.getInstance().open();

                if(this.tabStatus[0] == true){
                    request.getInstance().postData("api/profit/data",_data)
                    .then((res) => {

                        var _dataList = res.data.data.data;

                        if(_dataList.length == 0){
                            this.recordList = [];
                            Loading.getInstance().close();
                            return;
                        }

                        for(var i = 0; i <_dataList.length;i++){
                            _dataList[i].isTimePanel = false;
                        }
                        
                        this.recordList = _dataList;
                        this.buildTimePanel();
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
                } else if(this.tabStatus[1] == true){
                    var _data = {
                        limit:15,
                        offset:0
                    }

                    request.getInstance().postData("api/profit/withdraw/data",_data)
                    .then((res) => {

                        var _dataList = res.data.data.data;

                        if(_dataList.length == 0){
                            Loading.getInstance().close();
                            this.recordList = [];
                            return;
                        }
                        for(var i = 0; i <_dataList.length;i++){
                            _dataList[i].isTimePanel = false;
                        }
                        
                        this.recordList = _dataList;

                        this.buildTimePanel();
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
                }
                
            },

            changeTime(shijianchuo){
                function add0(m){return m<10?'0'+m:m }

                var time = new Date(shijianchuo*1000);
                var y = time.getFullYear();
                var m = time.getMonth()+1;
                var d = time.getDate();
                var h = time.getHours();
                var mm = time.getMinutes();
                var s = time.getSeconds();
                return y+'-'+add0(m)+'-'+add0(d)+' '+add0(h)+':'+add0(mm)+':'+add0(s);
            },
            
            changeTab(tabindex){
                this.tabStatus = [false,false];
                this.tabStatus[tabindex] = true;
                this.headList = [];
                this.init();
            },

            // 建立时间面板
            buildTimePanel(){
                var _head=0;

                var getTheDate = (timecode)=>{
                    if(!timecode){
                        return null;
                    }

                    var _index = timecode.indexOf("-");
                    if(_index == -1){
                        return null
                    }
                    var _t = timecode.split("-");
                    var data = _t[0]+"年"+_t[1]+"月";
                    return data;
                }
                
                var key = 0;
                // 设置头部
                if(this.recordList.length!=0){
                    if(this.recordList[0].isTimePanel == false){
                        key = 0;
                        var _head = getTheDate(this.recordList[key].created_at);
                    }else if(this.recordList[1].isTimePanel == false){
                        key = 1;
                        var _head = getTheDate(this.recordList[key].created_at);
                    }
                }

                // console.log("--------初始head----------");
                // console.log(_head);
                // console.log(this.headList);

                if(this.headList.length == 0){
                    // 分润列表
                    if(this.tabStatus[0] == true){
                        // 日期筛选 
                        if(this.dateChoise != null){
                            var _data = {
                                date:this.dateChoise
                            }

                            // 获取当月的总额度
                            request.getInstance().postData("api/profit/count",_data)
                                .then(res=>{
                                    var _initialData = {
                                        time:_head,
                                        index:key,
                                        total:res.data.data.total
                                    }
                                    
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;

                                }).catch();
                        }else {

                            // 获取当月的总额度
                            request.getInstance().postData("api/profit/count")
                                .then(res=>{
                                    var _initialData = {
                                        time:_head,
                                        index:key,
                                        total:res.data.data.total
                                    }
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;

                                }).catch();
                        }

                    }else if(this.tabStatus[1] == true){
                        // 提现列表
                        // 日期筛选 
                        if(this.dateChoise != null){
                            var _data = {
                                date:this.dateChoise
                            }

                            // 获取当月的总额度
                            request.getInstance().postData("api/profit/withdraw/count",_data)
                                .then(res=>{
                                    var _initialData = {
                                        time:_head,
                                        index:key,
                                        total:res.data.data.total
                                    }

                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;

                                }).catch();
                        }else {
                            // 获取当月的总额度
                            request.getInstance().postData("api/profit/withdraw/count")
                                .then(res=>{

                                    // var _initialData = {
                                    //     time:_head,
                                    //     index:key,
                                    //     total:res.data.data.total
                                    // }
                                    // console.log(3333);
                                    // console.log(_initialData);
                                    
                                    // this.headList.push(_initialData);
                                    // this.timeInfo = this.headList[0].time;
                                    // this.tabTotal = this.headList[0].total;
                                }).catch();
                        }
                        
                    }
                }
                
                 var _initialData = {
                    time:_head,
                    index:key,
                    total:"加载中..."
                }
                if(this.headList.length == 0){
                    this.headList.push(_initialData);
                }

                // 插入时间标签
                for(var i = 0; i <this.recordList.length; i++){
                    if(this.recordList[i].isTimePanel == true){
                        _head =getTheDate(this.recordList[i+1].created_at);
                        continue;
                    }

                    try{
                         var label = getTheDate(this.recordList[i].created_at);
                         
                        //  当头部与当前的创建时间不一致时
                       
                         if(_head != getTheDate(this.recordList[i].created_at)){
                            // 更新头部
                            _head = getTheDate(this.recordList[i].created_at);
                            
                            var data = {
                                time:_head,
                                index:i,
                                total:"加载中..."
                            }
                         
                            this.headList.push(data);
                          
                        }
                    }catch(e){
                        console.log(e);
                    }
                   
                }

                var count=  0;

                // recordList 插值
                for(let k=0 ;k<this.headList.length;k++){
                    var _index = this.headList[k].index+count;

                    if(this.recordList[_index].isTimePanel == true){
                        // console.log("跳过添加的这玩意是");
                        // console.log(this.recordList[_index]);
                        // console.log(this.headList[k]);
                        continue;
                    }
                    // console.log("要添加的是");
                    // console.log(this.headList[k]);
                    this.recordList.splice(_index,0,{isTimePanel:true,time:this.headList[k].time,total:this.headList[k].total});
                    count++;
                }

                for(let m = 0; m < this.recordList.length; m++){
                    if(this.recordList[m].isTimePanel == true && this.recordList[m].total == "加载中..."){

                        var _year = this.recordList[m].time.split("年")[0];
                        var _month = this.recordList[m].time.split("年")[1].split("月")[0];
                        var _timer = _year+"-"+_month;
                        var _data = {
                            date :_timer
                        }

                        if(this.tabStatus[0] == true){
                            // 获取当月的总额度(分润)
                            request.getInstance().postData("api/profit/count",_data)
                                .then(res=>{
                                    this.recordList[m].total = res.data.data.total;
                                    this.timeInfo = this.recordList[0].time;
                                    this.tabTotal = this.recordList[0].total;
                                }).catch();
                        }else {
                            // 获取当月的总额度(分润)
                            request.getInstance().postData("api/profit/withdraw/count",_data)
                                .then(res=>{
                                    this.recordList[m].total = res.data.data.total;
                                    this.timeInfo = this.recordList[0].time;
                                    this.tabTotal = this.recordList[0].total;
                                }).catch();
                        }
                        
                    }
                }
                count = 0;
            },

            // 滚动
            handleScroll(){
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                
                if(!this.$refs.timeTab){
                    return;
                }

                for(var i = 0; i< this.$refs.timeTab.length; i++){
                    if(this.$refs.timeTab[i].getBoundingClientRect().top <= "70" && this.$refs.timeTab[i].getBoundingClientRect().top >0){
                        if(i>1){
                            this.timeInfo = this.headList[i-1].time;
                            this.tabTotal = this.headList[i-1].total;
                        }
                    }
                  
                }
            },

            // 上滑加载更多
            loadMore() {
                this.loading =false;
                if(this.recordList.length==0 || !this.canLoading){
                    return;
                }

                this.loading = true;

                this.canLoading = false;

                setTimeout(() => {

                    var _data = {
                        limit:5,
                        offset :[].concat(this.recordList).pop().id,
                    }
                    
                    if (this.dateChoise!=null){
                        _data.date = this.dateChoise;
                    }

                    request.getInstance().postData('api/profit/data',_data).then(res=>{
                        if(res.data.data.data.length == 0){
                            this.canLoading = false;
                            this.loading = false;
                            return;
                        }
        
                        for(var i = 0; i< res.data.data.data.length; i ++){
                            res.data.data.data[i].isTimePanel = false;
                            this.recordList.push(res.data.data.data[i]);
                        }

                        this.canLoading = true;
                        this.loading = false;
                        this.buildTimePanel();
                    }).catch(err=>{

                        Loading.getInstance().close;
                        Toast(err.data.meg);

                    });
                }, 1500);
            },

            filterDate(){
                this.$refs.picker.open();
                // console.log(this.$refs.picker);
                // console.log(this.$refs.picker.$children[0].$children[0].$children[2]);
                this.$refs.picker.$children[0].$children[0].$children[2].$el.style.display = "none";
            },

            choiseDate(res){
                // this.dateChoise = null;
                this.headList = [];
                var _year = res.getFullYear();
                var _month = res.getMonth()+1;
                if(_month<10){
                    _month  = "0" + _month.toString();
                }
                var _date = _year+'-'+_month;
                this.dateChoise = _date;
                var _data = {
                    limit:15,
                    offset:0,
                    date:this.dateChoise
                }

                if(this.tabStatus[0] == true){
                    request.getInstance().postData("api/profit/data",_data)
                        .then((res) => {

                            var _dataList = res.data.data.data;

                            if(_dataList.length == 0){
                                Loading.getInstance().close();
                                this.recordList = [];
                                return;
                            }
                            for(var i = 0; i <_dataList.length;i++){
                                _dataList[i].isTimePanel = false;
                            }
                            
                            this.recordList = _dataList;
                            this.buildTimePanel();
                            Loading.getInstance().close();
                        })
                        .catch((err) => {
                            Toast(err.data.msg);
                            Loading.getInstance().close();
                        })
                }else if(this.tabStatus[1] == true){
                    request.getInstance().postData("api/profit/withdraw/data",_data)
                        .then((res) => {

                            var _dataList = res.data.data.data;

                            if(_dataList.length == 0){
                                Loading.getInstance().close();
                                this.recordList = [];
                                return;
                            }
                            for(var i = 0; i <_dataList.length;i++){
                                _dataList[i].isTimePanel = false;
                            }
                            
                            this.recordList = _dataList;
                            this.buildTimePanel();
                            Loading.getInstance().close();
                        })
                        .catch((err) => {
                            Toast(err.data.msg);
                            Loading.getInstance().close();
                        })
                }

                
            }

        },

        components: {
            topBack
        }
    };
</script>

<style lang="scss" scoped>

    .tab-fixed{
        position: fixed;
        top:5em;
        left: 0em;
        z-index:1001;
        width:100%;
        height:3em;
        background:#eee;
        box-sizing:border-box;
        padding-left: 1em;
        padding-right:1em;

        >div{
            color: #555;
            margin-top:0.3em;
        }
    }

    .time-tab{
        width:100%;
        // height:3em;
        background:#eee;
        padding:0;
        box-sizing:border-box;
        padding-left: 1em;
        padding-right:1em;

         >div{
            color: #555;
            margin-top:0.3em;
        }
    }

    .change-tab{
        width:100%;
        height: 3em;
        background:#26a2ff;
        position: fixed;
        top:2em;
        border:1px solid #fff;
        box-sizing: border-box;

        >div{
            color:#fff;
        }

        .active{
            background:#fff;
            color:#26a2ff;
        }
    }

    #profit-record {
        padding-top: 5em;
        box-sizing: border-box;
        .header-right {
            width: 100%;
            padding-right: 1em;
            height: 2em;
            box-sizing: border-box;
        }
    }

    .bill-box {
        font-size: 0.9em;
        /* height: 3.4em; */
        /* line-height: 1.7em; */
        /* background: #eee; */
        /* padding-top: 0.5em; */
        .bill-date {
            padding: 0 1em;
            color: #666;
        }
        .month-money {
            color: #999;
            .income {
                margin-left: 1em;
            }
        }

        ul{
            width: 100%;
            height: auto;
            display: block;
        }
    }

    .bill-list {
        li {
            padding: 0 1em;
            border-top: 1px solid #ccc;
            a {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 55px;
                line-height: 20px;
            }
            .bill-content {
                .time {
                    color: #999;
                    font-size: 0.8em;
                }
            }
            .bill-money {
                font-size: 1.2em;
            }
            .active {
                color: #00cc00;
            }
            &:last-child {
                border-bottom: 1px solid #ccc;
            }
        }
    }

    .slide-enter-active,
    .slide-leave-active {
        transition: all 1s ease;
    }

    .slide-enter,
    .slide-leave-to {
        transform: translateY(100vh);
    }

    .sel-type-box {
        h2 {
            height: 2.8em;
            line-height: 2.8em;
            text-align: center;
            color: #333;
            border: 1px solid #f1f1f1;
            font-weight: 700;
        }
        .type-list {
            padding: 4%;
            overflow: hidden;
            li {
                width: 32%;
                float: left;
                text-align: center;
                height: 60px;
                line-height: 60px;
            }
            a {
                display: block;
                width: 100%;
                height: 100%;
            }
            .active {
                a {
                    background: #50b56a;
                    color: #fff;
                }
            }
        }
    }

    .sel-type {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.2);
        z-index: 1000;
        transition: all 0.3s ease-in-out;
    }

    .sel-type-box {
        position: absolute;
        z-index: 1002;
        width: 100%;
        background: #fff;
        bottom: 0;
        border-radius: 4px;
    }

    .cancel-btn {
        margin-top: 1.5em;
    }
    .nodata{
        margin-top:20vh;
        i,div{
            color: #ddd;
        }
        i{
            font-size: 3.5em;
        }
        div{
            font-size: 2em;
            margin-top:0.3em;
        }
    }
</style>
