<template>
    <div id="makeDealTip">
        <topBack></topBack>
        <dealContent></dealContent>

         <div class="tip-wrap flex flex-align-center flex-justify-around" >
            <label for="">大赢家茶水费</label>
            <span style="color:#999;">请大家自觉缴纳</span>
            <span><i style="color:#999; font-size: 1.2em;">100.0</i>元</span>
        </div>

        <div class="button-wrap">
            <mt-button type="primary" size="large" class="green-color-bg">交纳</mt-button>
        </div>

        <div class="tip-record">
            <h3>茶水费记录</h3>
            <ul class="flex flex-v">
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
            </ul>

        </div>
    </div>
</template>

<style lang="scss" scoped>
#makeDealTip{
    min-height: 100vh;
    background: #eee;
}

.tip-wrap{
    width:90%;
    height: 2.5em;
    background:#fff;
    border-radius: 0.2em;
    margin:0 auto;
}

.button-wrap{
    width: 90%;
    margin:0 auto;
    margin-top:0.5em;
}

.green-color-bg{
    background: #11bb00;
}

.tip-record{
    margin-top:3em;
    h3{
        width:90%;
        margin:0 auto;
        font-size: 0.9em;
        color:#666;
    }

    ul{
        width: 90%;
        margin:0 auto;
        margin-top:0.5em;

        li{
            border-bottom: 1px solid #ccc;
            height: 3.2em;
            >img{
                width: 2.5em;
                height: 2.5em;
            }

            >span{
                color:#555;
            }
        }
    }
}
</style>

<script>
import topBack from "../../components/topBack"
import dealContent from "./dealContent"


export default{
    components:{topBack,dealContent}
}
</script>