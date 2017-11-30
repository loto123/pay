<template>
    <div id="withdraw" class="withdraw-container">
        <div class="title">提现金额</div>
        <div class="withdraw-money">
            <label>￥</label>
            <input type="text" placeholder="请输入金额">
        </div>
        <div class="all-money flex">
            <div class="money">可提现余额 ¥<span>231321</span>, </div>
            <a href="javascript:;" class="all-withdraw">全部提现</a>
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
    .withdraw-container{
        background: #fff;
        .tltle{
            font-size: 1em;
            color:#999;
        }
    }
    .withdraw-money{
        height: 40px;
        line-height: 40px;
        border-bottom: 1px solid #ccc;
        vertical-align: middle;
        margin-top: 1em;
        input{
            border: none;
            padding: 7px 0;
            display: inline-block;
            width: 60%;
        }
    }
    .all-money{
        margin-top:1em;
        font-size: 0.8em;
        .money{
            color:#666;
        }
        .all-withdraw{
            color:#199ED8;
            margin-left: 0.4em;
        }
    }
</style>


