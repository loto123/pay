<template>
    <div id="give" class="give-container">
        <topBack title="转账到店铺"></topBack>
        <div class="give-box">
            <div class="title">转账金额</div>
            <div class="give-money">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="all-money flex">
                <div class="money">可转账余额 ¥<span>231321</span>, </div>
                <a href="javascript:;" class="all-giveAcc">全部转账</a>
            </div>
            <div class="give-store">
                <h3>选择转账店铺</h3>
                <select name="" id="store">
                    <option value="0">默认店铺</option>
                </select>
            </div>
            <a href="javascript:;" class="transAcc-btn"> 
                <mt-button type="primary" size="large">转账</mt-button>    
            </a>
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
    .give-container{
        background:#eee;
        height: 100vh;
    }
    .give-box{
        background:#fff;
        padding:1em;
        margin:0 0.5em;
        .tltle{
            font-size: 1em;
            color:#999;
        }
    }
    .give-money{
        height: 40px;
        line-height: 40px;
        border-bottom: 1px solid #ccc;
        vertical-align: middle;
        margin-top: 1.2em;
        input{
            border: none;
            padding: 7px 0;
            display: inline-block;
            width: 70%;
        }
    }
    .all-money{
        margin-top:1em;
        font-size: 0.8em;
        .money{
            color:#666;
        }
        .all-giveAcc{
            color:#199ED8;
            margin-left: 0.4em;
        }
    }
    .give-store{
        margin-top: 2.5em;
        h3{
            color:#999;
            font-size: 0.9em;
        }
        #store{
            margin-top: 0.5em;
            width: 100%;
            height: 40px;
            line-height: 40px;
            border: 1px solid #ccc;
        }
    }
    .transAcc-btn{
        display: block;
        margin-top: 3em;
        margin-bottom:1em;
    }
</style>


