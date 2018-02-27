<template>
    <!-- 出售状态 -->
    <div id="status-list">
        <topBack title="出售状态" style="background:#fff;">
        </topBack>
        <div class="bill-box">
            <div class="bill-date flex flex-align-center flex-justify-between" style="display:none">
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
            
            <div class="tab-fixed flex flex-v flex-align-start" v-if="showList.length != 0">
                <div class="month">{{timeInfo==null?"加载中...":timeInfo}}</div>
                <div class="amount">出售获得：{{tabTotal}}</div>
            </div>

            <div v-if="showList.length == 0" class="flex flex-v flex-align-center nodata" >
                <i class="iconfont">
                    &#xe655;
                </i>
                <div>暂无数据</div>
            </div>
            
            <ul class="bill-list" v-else>
                <li  v-for="item in showList" class="flex flex-align-center" :class="{'time-panel-li':item.isTimePanel}">

                        <!-- 时间面板 -->
                        <div class="time-panel flex flex-align-center" v-if="item.isTimePanel == true" ref="timeTab">
                             <div class="bill-content flex-8 flex flex-v">
                                <!-- <h5></h5> -->
                                <div>{{item.time}}</div>
                                <div class="flex" style="margin-top:0.2em;">
                                    <div class="title" style="color:#999;">出售价格:</div>
                                    <div class="price" style="color:#999;">{{item.amount}}</div>
                                </div>  
                            </div>

                            <!-- <div class="bill-money flex-3">
                                
                            </div> -->
                        </div>
                        
                        <!-- 内容 -->
                        <div class="content flex flex-align-center" v-else>
                            <div class="imgWrap flex-2">
                            <img :src="item.pet_pic" alt="">
                            </div>

                            <div class="bill-content flex-8">
                                <h5>{{item.state}}</h5>
                                <div class="time">{{item.created_at}}</div>
                            </div>

                            <div class="bill-money flex-3">
                                <div class="title">出售价格:</div>
                                <div class="price">{{item.price}}</div>
                            </div>
                        </div>

                </li>
            </ul>
        </div>
   
    </div>
</template>

<script>
    import request from '../../utils/userRequest';
    import topBack from "../../components/topBack.vue";
    import Loading from '../../utils/loading'
    import { Toast } from "mint-ui";

    export default {
        data() {
            return {
                showAlert: false,
                type:null,      //类型
                created_at:null,        //结束时间
                size:null,  //数目

                billList:[],
                originList:[],
                showList :[],

                timeInfo:null,
                tabTotal:"",
                headList:[],

                wrapperHeight:null,
                loading: false,
                allLoaded: false,
                canLoading:true,
            };
        },
        created(){
            this.init();
        },
        methods: {
            show() {
                this.showAlert = true;
            },
            cancel() {
                this.showAlert = false;
            },
            
            init(){
                var data={
                    type:this.type,
                    created_at:this.created_at,
                    limit:10,
                    version:2
                }

                Loading.getInstance().open();

                request.getInstance().getData("api/pet/sold_record",data)
                    .then((res) => {

                        // 获取事件分组
                        this.originList = res.data.data.grouping;

                        this.billList=[].concat(this.originList);

                        // for(var i = 0; i <this.billList.length;i++){
                        //     this.billList[i].isTimePanel = false;
                        // }

                        this.tabTotal = res.data.data.sold_amount;
                        
                        this.showList = this.buildDataList();

                        if(this.showList.length>0){
                            this.timeInfo = this.showList[0].time;
                            this.tabTotal = this.showList[0].amount;
                        }

                        Loading.getInstance().close();
                        
                    })
                    .catch((err) => {
                        console.error(err);
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
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
                if(this.billList.length!=0){
                    if(this.billList[0].isTimePanel == false){
                        key = 0;
                        var _head = getTheDate(this.billList[key].created_at);
                    }else if(this.billList[1].isTimePanel == false){
                        key = 1;
                        var _head = getTheDate(this.billList[key].created_at);
                    }
                }

                console.log(_head);

                var _initialData = {
                    time:_head,
                    index:key,
                    total:"加载中..."
                }
                if(this.headList.length == 0){
                    this.headList.push(_initialData);
                }

                // 插入时间标签
                for(var i = 0; i <this.billList.length; i++){
                    if(this.billList[i].isTimePanel == true){
                        _head =getTheDate(this.billList[i+1].created_at);
                        continue;
                    }

                    try{
                         var label = getTheDate(this.billList[i].created_at);
                         
                        //  当头部与当前的创建时间不一致时
                       
                         if(_head != getTheDate(this.billList[i].created_at)){
                            // 更新头部
                            _head = getTheDate(this.billList[i].created_at);
                            
                            var data = {
                                time:_head,
                                index:i,
                                total:"加载中..."
                            }
                         
                            this.headList.push(data);
                          
                        }
                    }catch(e){
                        console.error(e);
                    }
                   
                }

                var count=  0;

                // billList 插值
                for(let k=0 ;k<this.headList.length;k++){
                    var _index = this.headList[k].index+count;

                    if(this.billList[_index].isTimePanel == true){
                        continue;
                    }
                    this.billList.splice(_index,0,{isTimePanel:true,time:this.headList[k].time,total:this.headList[k].total});
                    count++;
                }

                for(let m = 0; m < this.billList.length; m++){
                    if(this.billList[m].isTimePanel == true && this.billList[m].total == "加载中..."){
                        console.log(m);
                        console.log(this.billList[m].time.split("年")[0]);

                        var _year = this.billList[m].time.split("年")[0];
                        var _month = this.billList[m].time.split("年")[1].split("月")[0];
                        var _timer = _year+"-"+_month;

                        var _data = {
                            date :_timer
                        }
                        console.log(_data);
                        this.timeInfo = this.billList[0].time;

                        // request.getInstance().postData().then(res=>{

                        // }).catch(err=>{

                        // });

                        // if(this.tabStatus[0] == true){
                        //     // 获取当月的总额度(分润)
                        //     request.getInstance().postData("api/profit/count",_data)
                        //         .then(res=>{
                        //             this.billList[m].total = res.data.data.total;
                        //             this.timeInfo = this.billList[0].time;
                        //             this.tabTotal = this.billList[0].total;
                        //         }).catch();
                        // }else {
                        //     // 获取当月的总额度(分润)
                        //     request.getInstance().postData("api/profit/withdraw/count",_data)
                        //         .then(res=>{
                        //             this.billList[m].total = res.data.data.total;
                        //             this.timeInfo = this.billList[0].time;
                        //             this.tabTotal = this.billList[0].total;
                        //         }).catch();
                        // }
                        
                    }
                }
                count = 0;
            },

            // 建立数据列表
            buildDataList(){
                var _dataList = [];

                for (var i = 0; i< this.billList.length; i ++){

                    var _timePanelInfo = {};
                    _timePanelInfo.time = this.billList[i].month;
                    _timePanelInfo.amount = this.billList[i].sold_amount;
                    _timePanelInfo.isTimePanel = true;

                    this.billList[i].list.unshift(_timePanelInfo);

                    _dataList = _dataList.concat(this.billList[i].list);
                }

                return _dataList;
                console.log(_dataList);
            }



        },
        components: {
            topBack
        }
    };
</script>

<style lang="scss" scoped>
    #status-list {
        padding-top: 2em;
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
    }

    .tab-fixed{
        position: fixed;
        top:2em;
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

    .bill-list {
        /*padding-top:2em;*/
        li {
            padding: 0 1em;
            border-top: 1px solid #ccc;
            width: 100%;
            height: 4em;
            box-sizing: border-box;

            .content{
                width: 100%;
                height: 4em;
                .imgWrap{
                    >img{
                        width:100%;
                    }
                }
            }
            

            .bill-content {
                padding-left: 0.7em;
                padding-right: 0.7em;
                box-sizing: border-box;

                .time {
                    color: #999;
                    font-size: 0.8em;
                    margin-top:0.5em;
                }

            }
            .bill-money {
                font-size: 1em;
                .price{
                    margin-top:0.5em;
                }
            }
            .active {
                color: #00cc00;
            }
            &:last-child {
                border-bottom: 1px solid #ccc;
            }
        }

        .time-panel-li{
            background: #eee;
            height: 3em;

            .time-panel{
                width: 100%;
                height: 100%;
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
        margin-top:10%;
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