<!-- 我的账户 -->
<template>
    <div class="myAccount-container">
        <mt-header title="标题过长会隐藏后面的内容啊哈哈哈哈">
            <router-link to="/" slot="left">
                <mt-button icon="back">返回321</mt-button>
            </router-link>
            <mt-button icon="more" slot="right"></mt-button>
        </mt-header>
    
        <section class="withDraw-money-box">
            <div class="withDraw-money">231321</div>
        </section>

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
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 3em auto 3em auto;
        background: #26a2ff;
        color: #fff;
        .withDraw-money{
            text-align: center;
        }
    }
    .withdraw_btn{
        display: block;
        width: 100%;
        width: 96%;
        margin: auto;
    }
    .recharge_btn{
        display: block;
        width: 100%;
        width: 96%;
        margin: auto;
        margin-top: 2em;
    }
</style>


