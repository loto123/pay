<template>
    <div id="myAccount" class="myAccount-container">
        <topBack title="我的钱包" style="background:#26a2ff;color:#fff;">
            <div class= "flex flex-reverse flex-align-center header-right">
                <a href="/#/myAccount/bill" style="color:#fff;">账单明细</a>
            </div>
        </topBack>
        <div class="myAccount-box">
            <div class="withDraw-money">
                <div class="money flex flex-align-center flex-justify-center">{{balance}}<i class="diamond" style="margin-top: -0.1em;margin-left:0.4em;">&#xe6f9;</i></div>
                <div class="title">当前可用钻石</div>
            </div>

            <ul class="myAccount-list">
				<li @click="purchase">
					<mt-cell title="购买" is-link></mt-cell>
                </li>
                <li @click="withdraw">
					<mt-cell title="出售" is-link></mt-cell>
                </li>
                <li @click="give">
					<mt-cell title="转钻到公会" is-link></mt-cell>
                </li>
            </ul>
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
            //购买
            purchase(){
                this.$router.push('/myAccount/recharge')
            },
            //出售
            withdraw(){
                this.$router.push('/myAccount/withdraw')
            },
            //转钻到公会
            give(){
                if(this.has_pay_card==0){
                    MessageBox.confirm("您还没有绑定银行卡,是否前往绑定！", "温馨提示").then(
                        () => {
                            this.$router.push('/my');
                        },
                        () => {
                            //取消操作
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
        /* margin-top: 2em; */
    }
    .withDraw-money{
        height: 13em;
        background: #26a2ff;
        position: relative;
        text-align: center;
        color: #fff;
        .money,.title{
            position: absolute;
            width:100%;
            text-align: center;
        }
        .money{
            top: 30%;
            color: #fff;
            font-size: 1.8em;
        }
        .title{
            top: 50%;
            color:#ddd;
        }
    }
    .myAccount-list {
		border-bottom: 1px solid #d9d9d9;
		li {
			.mint-cell {
				background-image: none;
				background-size: 100% 1px;
				background-repeat: no-repeat;
				background-position: top;
				span {
					font-size: 0.9em;
				}
			}
		}
	}
    .mint-button--danger{
        background:#fff !important;
    }
    .diamond {
        color: orange;
        font-size: 0.8em;
    }
</style>


