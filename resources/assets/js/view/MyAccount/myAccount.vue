<template>
    <div id="myAccount" class="myAccount-container">
        <topBack title="我的账户">
            <div style="width:100%; padding-right:1em; height:2em; box-sizing:border-box;" class= "flex flex-reverse flex-align-center">
                <a  style="width:4em;" href="/#/myAccount/bill" class="recharge-btn ">账单明细</a>
            </div>
        </topBack>
        <div class="myAccount-box">
            <div class="withDraw-money">
                <div class="money">88.88</div>
                <div class="title">当前可用余额</div>
            </div>
            <div class="submit-btn">
                <a href="/#/myAccount/recharge" class="mb15">
                    <button type="button" class="recharge-btn">充值</button>
                </a>
                <a href="/#/myAccount/withdraw" class="mb15">
                    <button type="button" class="withdraw-btn">提现</button>
                </a>  
                <a href="/#/myAccount/give">
                    <button type="button" class="give-btn">转账到店铺</button>    
                </a>  
            </div>
        </div>
    </div>
</template>

<script>
    import topBack from '../../components/topBack.vue'
    export default {
        data () {
            return {
                
            }
        },
        components: {topBack}
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
    }
    .withDraw-money{
        width: 14em;
        height: 14em;
        border: 1px solid #aaa;
        border-radius: 50%;
        margin: 0 auto 3em auto;
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


