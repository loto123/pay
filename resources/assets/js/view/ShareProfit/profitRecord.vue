<template>
    <div id="profit-record">
        <topBack title="账单明细"
            style="background:#26a2ff;color:#fff;"
        >
            <div class="flex flex-reverse flex-align-center header-right">
                <!-- <a href="javascript:;" @click="show">筛选</a> -->
            </div>
        </topBack>

        <div class="change-tab flex">
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(0)" :class="{active:tabStatus[0]}">收益明细</div>
            <div class="flex-1 flex flex-justify-center flex-align-center" @click="changeTab(1)" :class="{active:tabStatus[1]}">提现记录</div>
        </div>

        <div class="tab-fixed flex flex-v flex-align-start" v-if="recordList.length != 0">
            <div class="month">{{timeInfo}}</div>
            <div class="amount">{{tabStatus[0]?'收益：':'提现：'}}100</div>
           
        </div>
        <div class="bill-box">
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

            <ul class="bill-list" v-else>
 
                <li  v-for="item in recordList" @click="details(item.id)" :class="{'time-tab':item.isTimePanel}">
                    <a href="javascript:;" class="flex" v-if="item.isTimePanel == false">
                        <div class="bill-content">
                            <h5>{{status(item.type)}}</h5>
                            <div class="time">{{item.created_at}}</div>
                        </div>
                        <div class="bill-money" v-bind:class="[item.mode == 1?'':'active']">{{item.mode == 1?-item.amount:item.amount}}</div>
                    </a>

                    <div v-if="item.isTimePanel == true" class="time-tab" ref="timeTab">
                        <div class="month">{{item.time}}</div>
                        <div class="amount">100</div>
                    </div>
                </li>
            </ul>
        </div>
        <!-- <transition name="slide">
            <div class="sel-type" v-if="showAlert">
                <div class="sel-type-box">
                    <h2>选择交易类型</h2>
                    <ul class="type-list">
                        <li class="active">
                            <a href="javascript:;">全部</a>
                        </li>
                        <li>
                            <a href="javascript:;">交易</a>
                        </li>
                        <li>
                            <a href="javascript:;">店铺转账</a>
                        </li>
                        <li>
                            <a href="javascript:;">全部</a>
                        </li>
                        <li>
                            <a href="javascript:;">交易</a>
                        </li>
                        <li class="active">
                            <a href="javascript:;">全部</a>
                        </li>
                        <li>
                            <a href="javascript:;">交易</a>
                        </li>
                        <li>
                            <a href="javascript:;">店铺转账</a>
                        </li>
                        <li>
                            <a href="javascript:;">全部</a>
                        </li>
                        <li>
                            <a href="javascript:;">交易</a>
                        </li>
                    </ul>
                    <div class="cancel-btn">
                        <a href="javascript:;" @click="cancel">
                            <mt-button type="default" size="large">取消</mt-button>
                        </a>
                    </div>
                </div>
            </div>
        </transition> -->
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
                type:null,		//类型
                created_at:null,		//结束时间
                size:null,  //数目
                recordList:[],

                _headList:[],      // timeTab数组
                timeInfo:"",
                tabStatus:[true,false]
            };
        },
        created(){
            this.init();
        },

        mounted(){
            window.addEventListener('scroll', this.handleScroll)
        },

        methods: {
            show() {
                this.showAlert = true;
            },
            cancel() {
                this.showAlert = false;
            },
            details(id) {
                this.$router.push({ path: "/shop/record/record_details?id="+id});
            },

            init(){
                var data={
                    type:this.type,
                    created_at:this.created_at,
                    size:this.size
                }
                // Loading.getInstance().open("加载中...");
                // this.shopId = this.$route.query.id;
                // this.recordList  = [{
                //     type:"分润",
                //     created_at:"2017-1-1",
                //     mode:1,
                //     amount:100,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-1-1",
                //     mode:1,
                //     amount:100,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-2-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },
                // {
                //     type:"分润",
                //     created_at:"2017-2-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },
                // {
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },
                // {
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-3-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // },{
                //     type:"分润",
                //     created_at:"2017-4-1",
                //     mode:1,
                //     amount:200,
                //     isTimePanel:false
                // }
                // ];

                var _data = {
                    limit:10,
                    offset:0
                }
                
                Loading.getInstance().open();
                request.getInstance().postData("api/profit/data",_data)
                    .then((res) => {

                        var _dataList = res.data.data;

                        for(var i = 0; i <_dataList.length;i++){
                            _dataList[i].isTimePanel = false;
                        }
                        
                        this.recordList = _dataList;
                        this.buildTimePanel();
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        // console.log(err);
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
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
            },

            status(type){
                let result='';
                switch(type){
                    case 0: result='充值'; break;
                    case 1: result='提现'; break;
                    case 2: result='交易收入'; break;
                    case 3: result='交易支出'; break;
                    case 4: result='转账到店铺'; break;
                    case 5: result='店铺转入'; break;
                    case 6: result='交易手续费'; break;
                    case 7: result='提现手续费'; break;
                    default: result='打赏店家费'
                }
                return result;
            },

            // 建立时间面板
            buildTimePanel(){

                var _head=0;
                this._headList = [];
                var getTheDate = (timecode)=>{
                    var _t = timecode.split("-");
                    var data = _t[0]+"年"+_t[1]+"月";
                    return data;
                }

                if(this.recordList.length!=0){
                    var _head = this.recordList[0];
                }

                for(var i = 0; i <this.recordList.length; i++){
                    if(_head == 0){
                        _head = getTheDate(this.recordList[i].created_at);
                        var data = {
                            time:_head,
                            index:i
                        }
                        this._headList.push(data);
                    }

                    if(_head != getTheDate(this.recordList[i].created_at)){
                        _head = getTheDate(this.recordList[i].created_at);
                        var data = {
                            time:_head,
                            index:i
                        }
                        this._headList.push(data);
                    }
                    
                }

                console.log(this._headList);
                var count=  0;

                this.timeInfo = this._headList[0].time;

                // 插入数值
                for(var k=0 ;k<this._headList.length;k++){
                    this.recordList.splice(this._headList[k].index+count,0,{isTimePanel:true,time:this._headList[k].time});
                    count++;
                }

            },
            handleScroll(){
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

                for(var i = 0; i< this.$refs.timeTab.length; i++){
                    if(this.$refs.timeTab[i].getBoundingClientRect().top <= "70" && this.$refs.timeTab[i].getBoundingClientRect().top >0){
                        this.timeInfo = this._headList[i].time;
                    }
                    
                    // if(this.$refs.timeTab[i].getBoundingClientRect().top > "30") {
                    //     // this.$refs.timeTab[i].className ="";
                    // }
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
        height:3em;
        background:#eee;
        padding:0;
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
