<!-- 我的账户 -->
<template>
    <div class="myAccount-container">
        <div class="withDraw-money-box">
            <div class="withDraw-money">231321</div>
            <div>当前可余额</div>
        </div>
        <div>
            <a href="javascript:;" class="withdraw_btn">
                <mt-button size="large" type="primary">提现</mt-button>    
            </a>

            <a href="javascript:;" class="recharge_btn">
                <mt-button size="large" type="danger">充值</mt-button>    
            </a>    
        </div>
    </div>
</template>

<script>
    export default {
        data () {
            return {
                
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
    .withDraw-money-box{
        width: 15em;
        height: 15em;
        border: 1px solid #aaa;
        border-radius: 50%;
        margin: 3em auto 3em auto;
        background: #26a2ff;
        color: #fff;
        .withDraw-money{
            text-align: center;
        }
    }
    .withdraw_btn,.recharge_btn{
        display: block;
        width: 100%;
        width: 96%;
        margin: auto;
    }
    .recharge_btn{
        margin-top: 2em;
    }
</style>


