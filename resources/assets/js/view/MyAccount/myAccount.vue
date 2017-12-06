<template>
    <div class="myAccount-container">
        <topBack title="我的账户"></topBack>
        <div class="myAccount-box">
            <a href="/#/myAccount/bill" class="recharge-btn flex">账单明细</a>
            <div class="withDraw-money">
                <div class="money">88.88</div>
                <div class="title">当前可用余额</div>
            </div>
            <div class="submit-btn">
                <a href="" class="recharge-btn">
                    <mt-button size="large" type="primary">充值</mt-button>    
                </a>
                <a href="/#/myAccount/withdraw" class="withdraw-btn">
                    <mt-button size="large" type="danger">提现</mt-button>    
                </a>  
                <a href="/#/myAccount/give" class="give-btn">
                    <mt-button size="large" type="danger">转账到店铺</mt-button>    
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
    .myAccount-box{
        background:#fff;
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
    .withdraw-btn,.recharge-btn,.give-btn{
        display: block;
        width: 100%;
        margin: auto;
    }
    .withdraw-btn,.give-btn{
        margin-top: 1.5em;
    }
</style>


