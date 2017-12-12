<template>
    <div id="shop-account">
        <div class="top">
            <top-back :title="'店铺账户'" style="background: #26a2ff; color:#fff;"></top-back>
            <div class="profit-wrap flex flex-align-center">
                <div class="left flex flex-v flex-align-center flex-justify-around">
                    <div class="money">69999.99</div>
                    <div class="title">昨日收益(元)</div>
                </div>
                <div class="right flex flex-v flex-align-center flex-justify-around">
                    <div class="money">69999.99</div>
                    <div class="title">今日收益(元)</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
#shop-account{
    padding-top:2em;
    min-height: 100vh;
    background: #ecf6ff;

    .top{
        height:8em;
        background: #26a2ff;

        .profit-wrap{
            height:4em;
            width:90%;
            margin:0 auto;
            padding-top:2em;
            .left{
                height: 4em;
                box-sizing: border-box;
                border-right: 1px solid #fff;
                width: 50%;

                >div{
                    color:#fff;
                }

                .money{
                    font-size: 1.4em;
                }
            }

            .right{
                height: 4em;
                width: 50%;

                >div{
                    color:#fff;
                }

                .money{
                    font-size: 1.4em;
                }
            }
        }
    }
}
</style>

<script>
import topBack from "../../components/topBack"

export default{
    components:{topBack}
}

</script>