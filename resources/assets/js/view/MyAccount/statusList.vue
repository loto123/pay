<template>
    <!-- 出售状态 -->
    <div id="status-list">
        <topBack title="出售状态">
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
            
            <div class="tab-fixed flex flex-v flex-align-start" v-if="billList.length != 0">
                <div class="month">{{timeInfo==null?"加载中...":timeInfo}}</div>
                <div class="amount">出售获得：{{tabTotal}}</div>
            </div>

            <div v-if="billList.length == 0" class="flex flex-v flex-align-center nodata" >
                <i class="iconfont">
                    &#xe655;
                </i>
                <div>暂无数据</div>
            </div>
            
            <ul class="bill-list" v-else>
                <li  v-for="item in billList" class="flex flex-align-center">

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
                items:[
                    {type:0,title:'充值'},
                    {type:1,title:'提现'},
                    {type:2,title:'交易收入'},
                    {type:3,title:'交易支出'},
                    {type:4,title:'转账到公会'},
                    {type:5,title:'公会转入'},
                    {type:8,title:'打赏店家费'},
                ],
                selected: null,

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
            // details(id) {
            //     this.$router.push({ path: "/myAccount/bill/bill_details?id="+id});
            // },
            init(){
                var data={
                    type:this.type,
                    created_at:this.created_at,
                    limit:10
                }
                Loading.getInstance().open();

                request.getInstance().getData("api/pet/sold_record",data)
                    .then((res) => {
                        this.billList=res.data.data.list;

                        for(var i = 0; i <this.billList.length;i++){
                            this.billList[i].isTimePanel = false;
                        }

                        Loading.getInstance().close();

                        this.buildTimePanel();
                    })
                    .catch((err) => {
                        console.error(err);
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
            status(type){
                let result='';
                switch(type){
                    case 0: result='充值'; break;
                    case 1: result='提现'; break;
                    case 2: result='交易收入'; break;
                    case 3: result='交易支出'; break;
                    case 4: result='转账到公会'; break;
                    case 5: result='公会转入'; break;
                    case 6: result='交易手续费'; break;
                    case 7: result='提现手续费'; break;
                    case 8: result='打赏店家费'; break;
                }
                return result;
            },
            selContent(type){
                Loading.getInstance().open("加载中...");
                request.getInstance().getData("api/account/records?type="+type)
                    .then((res) => {
                        this.billList=res.data.data.data;
                        this.showAlert = false;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            selAll(){
                this.init();
                this.showAlert = false;
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
           
            .imgWrap{
                >img{
                    width:100%;
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