<template>
    <div id="myAccount" class="myAccount-container">
        <topBack title="我的钱包" style="background:#eee;">
            <div class= "flex flex-reverse flex-align-center header-right">
                <a href="/#/myAccount/bill" class="recharge-btn ">账单明细</a>
            </div>
        </topBack>
        <div class="myAccount-box">
            <div class="withDraw-money">
                <div class="money">{{balance}}</div>
                <div class="title">当前可用钻石</div>
            </div>
            <div class="submit-btn">
                <a href="/#/myAccount/recharge" class="mb15">
                    <button type="button" class="recharge-btn">购买(充值)</button>
                </a>
                <a href="javascript:;"  @click="withdraw" class="mb15">
                    <button type="button" class="withdraw-btn">出售(提现)</button>
                </a>  
                <a href="javascript:;" @click="give">
                    <button type="button" class="give-btn">转钻到公会</button>    
                </a>  
            </div>
        </div>
    </div>
</template>

<script>
	import request from '../../utils/userRequest';
    import topBack from '../../components/topBack.vue'
    import Loading from '../../utils/loading'
    import { MessageBox, Toast } from "mint-ui";

    export default {
        data () {
            return {
                balance:null,
                has_pay_card:null//是否有结算卡
            }
        },
        created(){
			this.myAccount();
    	},
        components: {topBack},
        methods: {
            myAccount(){
                Loading.getInstance().open("加载中...");

				request.getInstance().getData("api/account")
					.then((res) => {
                        this.balance=res.data.data.balance;
                        this.has_pay_card=res.data.data.has_pay_card;
                        Loading.getInstance().close();
					})
					.catch((err) => {
						Toast(err.data.msg);
                        Loading.getInstance().close();
					})
            },
            //提现
            // withdraw(){
            //     if(this.has_pay_card==0){
            //         MessageBox.confirm("您还没有绑定银行卡,是否前往绑定！", "温馨提示").then(
            //             () => {
            //                 this.$router.push('/my');
            //             },
            //             () => {
            //                 //取消操作
            //                 console.log("已经取消");
            //             }
            //         );
            //     }else{
            //         this.$router.push('/myAccount/withdraw')
            //     }
            // },
            //转账
            give(){
                if(this.has_pay_card==0){
                    MessageBox.confirm("您还没有绑定银行卡,是否前往绑定！", "温馨提示").then(
                        () => {
                            this.$router.push('/my');
                        },
                        () => {
                            //取消操作
                            console.log("已经取消");
                        }
                    );
                }else{
                    this.$router.push('/myAccount/give')
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
    .mb15{
        margin-bottom:1.5em;
    }
    #myAccount {
        background: #eee;
        height: 100vh;
        padding-top: 2em;
        box-sizing: border-box;
        .header-right{
            width:100%;
            padding-right:1em;
            height:2em;
            box-sizing:border-box;
        }
    }
    .myAccount-box{
        margin-top: 2em;
    }
    .withDraw-money{
        width: 13em;
        height: 13em;
        border: 1px solid #aaa;
        border-radius: 50%;
        margin: 0 auto 2em auto;
        background: #199ED8;
        position: relative;
        text-align: center;
        .money,.title{
            position: absolute;
            width:100%;
            text-align: center;
        }
        .money{
            top: 30%;
            color: #fff;
            font-size: 2em;
        }
        .title{
            top: 50%;
            color:#ddd;
        }
    }
    .submit-btn{
        width: 90%;
        margin:auto;
        a{
            display: block;
            button{
                border: none;
                height: 2.8em;
                line-height: 2.8em;
                text-align: center;
                width: 100%;
                margin: auto;
                border-radius: 5px;
                font-size: 1em;
            }
            .recharge-btn{
                background:#00CC00;
                color: #fff;
            }
            .withdraw-btn{
                background:#fff;
                color: #333;
            }
            .give-btn{
                background:#199ED8;
                color:#fff;
            }
        }
    }
    .mint-button--danger{
        background:#fff !important;
    }
</style>


