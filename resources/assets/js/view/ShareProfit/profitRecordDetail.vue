<template>
    <div id="profit-record-detail">
        <topBack title="账单明细"></topBack>
        <div class="details-content">
            <div class="money-box">
                <span>收益金额</span>
                <em class="active">{{amount}}</em>
            </div>
            <ul class="billDetails-list">
                <li>
                    <div class="title">类型</div>
                    <div class="content">{{type}}</div>
                </li>
                <li>
                    <div class="title">时间</div>
                    <div class="content">{{created_at}}</div>
                </li>
                <li>
                    <div class="title">收益来源人</div>
                    <div class="content">{{nick}}</div>
                </li>
                <li>
                    <div class="title">收益来源人账号</div>
                    <div class="content">{{nickAccount}}</div>
                </li>
               
            </ul>
        </div>
    </div>
</template>

<script>
    import request from '../../utils/userRequest';
    import topBack from "../../components/topBack.vue";
    import Loading from '../../utils/loading'
    import { MessageBox, Toast } from "mint-ui";
    export default {
        data() {
            return {
                showAlert: false,
                created_at:null,	//时间
                type:null,			//类型
                amount:null,		//入账金额
                mode:null,		
                nick:null,
                nickAccount:null	
            };
        },
        created(){
            this.init();
        },
        methods: {
            init(){
                Loading.getInstance().open("加载中...");
                var self = this;
                var _id = this.$route.query.id;
               
                request.getInstance().getData("api/profit/show/"+_id)
                    .then((res) => {
                        
                        this.created_at=res.data.data.created_at.date;
                        this.amount=res.data.data.proxy_amount;
                        this.type=res.data.data.type;
                        this.nick = res.data.data.user_nick;
                        this.nickAccount = res.data.data.user_mobile;
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
                let res='';
                switch(type){
                    case 0: res='提现'; break;
                    case 1: res='转账'; break;
                    case 2: res='收入'; break;
                }
                return res;
            }
        },
        components: {
            topBack
        }
    };
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
    #profit-record-detail {
        padding-top: 2em;
        background: #eee;
        height: 100vh;
        box-sizing: border-box;
    }

    .details-content {
        background: #fff;
        padding-bottom: 4em;
    }

    .money-box {
        height: 40px;
        line-height: 40px;
        border-bottom: 1px solid #ccc;
        display: flex;
        justify-content: space-between;
        padding: .5em .7em 0 .7em;
        .active{
            color: #00cc00;
        }
    }

    .billDetails-list {
        li {
            margin-top: 1em;
            display: flex;
            justify-content: space-between;
            padding: 0 0.7em;
            .title {
                color: #333;
            }
            .content {
                color: #555;
            }
            .remark{
                width: 20px;
            }
        }
    }
</style>