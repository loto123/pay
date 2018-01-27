<template>
    <!-- 出售状态 -->
    <div id="status-list">
        <topBack title="账单明细">
            <!-- <div class="flex flex-reverse flex-align-center header-right" @click="show">
                <a href="javascript:;">筛选</a>
            </div> -->
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
            <div v-if="billList.length == 0" class="flex flex-v flex-align-center nodata" >
                <i class="iconfont">
                    &#xe655;
                </i>
                <div>暂无数据</div>
            </div>
            <ul class="bill-list" v-else>
                <li  v-for="item in billList" class="flex flex-align-center">

                        <div class="imgWrap flex-1">
                            <img src="/images/avatar.jpg" alt="">
                        </div>

                        <div class="bill-content flex-6">
                            <h5>{{item.state}}</h5>
                            <div class="time">{{item.created_at}}</div>
                        </div>
                        <div class="bill-money flex-1"  v-bind:class="[item.mode == 1?'':'active']">{{item.price}}</div>
                </li>
            </ul>
        </div>
        <transition name="slide">
            <div class="sel-type" v-if="showAlert">
                <div class="sel-type-box">
                    <h2>选择任务类型</h2>
                    <ul class="type-list">
                        <li @click="selAll">
                            <a href="javascript:;">全部</a>
                        </li>
                        <li v-for="item in items" @click="selContent(item.type)">
                            <a href="javascript:;">{{item.title}}</a>
                        </li>
                    </ul>
                    <div class="cancel-btn">
                        <a href="javascript:;" @click="cancel">
                            <mt-button type="default" size="large">取消</mt-button>
                        </a>
                    </div>
                </div>
            </div>
        </transition>
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
                selected: null
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
                        // console.log(res);
                        this.billList=res.data.data.list;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
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
            width: 100%;
            height: 3em;
            box-sizing: border-box;
           
            .imgWrap{
                >img{
                    width:100%;
                }
            }

            .bill-content {
                padding-left: 0.5em;
                padding-right: 0.5em;
                box-sizing: border-box;
                
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