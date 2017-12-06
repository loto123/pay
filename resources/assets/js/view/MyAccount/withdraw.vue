<template>
    <div id="withdraw" class="withdraw-container">
        <topBack title="提现"></topBack>
        <div class="withdraw-box">
            <div class="title">提现金额</div>
            <div class="withdraw-money">
                <label>￥</label>
                <input type="text" placeholder="请输入金额">
            </div>
            <div class="all-money flex">
                <div class="money">可提现余额 ¥<span>231321</span>, </div>
                <a href="javascript:;" class="all-withdraw">全部提现</a>
            </div>
            <section class="way">
                <div class="xx">
                  <input id="radio-input-1" type="radio" value="myValue 1" name="radio-group" checked>
                  <label for="radio-input-1" class="input-helper input-helper--radio">myValue 1</label>
                    
                    <input id="radio-input-2" type="radio" value="myValue 2" name="radio-group">
                    <label for="radio-input-2" class="input-helper input-helper--radio">myValue 2</label>
                </div>
            </section>
            <a href="javascript:;" class="withdraw-btn"> 
                <mt-button type="primary" size="large">提现</mt-button>    
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
    .withdraw-container{
        background:#eee;
        height: 100vh;
    }
    .withdraw-box{
        background:#fff;
        padding:1em;
        margin:0 0.5em;
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
        .all-withdraw{
            color:#199ED8;
            margin-left: 0.4em;
        }
    }
    .way{
        margin-top:1em;

        div{
            position: relative;
            line-height: 30px;
        }
        input{
            width: 20px;
            height: 20px;
            opacity: 0;
        }
        
        label {
            position: absolute;
            left: 5px;
            top: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 1px solid #999;
        }
    }
    // input[type="radio"]:checked {
    //     + label {
    //         background-color: green;
    //         border: 1px solid green;
    //     }
    // }
    .withdraw-btn{
        display: block;
        margin-top: 3em;
        margin-bottom:1em;
    }
    .input-helper {
    position: relative;
    display: inline-block;
    
    &:before {
        content: '';
        display: block;
        position: absolute;
    }
}

.input-helper--radio {
    padding-left: 18px;

    &:before {
        top: 1px;
        left: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 1px solid #222;
    }
}
input[type="radio"] {
    display: none;

    &:checked + label:before {
        background: #222;
    }
}
</style>


