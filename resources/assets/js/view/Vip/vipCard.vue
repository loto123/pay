<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="color:#fff;background:#26a2ff;" :title="'VIP开卡'">
                <div class="flex flex-reverse flex-align-center header-right">
                    <a href="#" class="option-record">操作记录</a>
                </div>
            </topBack>
            <div class="card-amount">
                <div>6</div>
                <h3>已出卡数(张)</h3>
            </div>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="option-card flex flex-align-center flex-justify-center active">开卡转卡</div>
            <div class="auth flex flex-align-center flex-justify-center" @click="goGeneralize">推广授权</div>
        </div>
        <div class="infos flex flex-align-center">
            <div class="icon">
                <i class="iconfont" style="font-size:1.5em;color:#26a2ff">
                    &#xe6e1;
                </i>
            </div>
            <div class="flex-3">我的VIP卡(3张)</div>
        </div>

        <ul class="card-list">
            <li class="flex flex-align-center" @click="openCard">
                <div class="flex-3 card-number">NO.123456</div>
                <div class="flex-6 card-type">
                    <div class="type">
                        <em>VIP</em>
                        <span>金卡</span>
                    </div>
                    <div class="share-profit">尊享分润比例：5‰</div>
                </div>
                <div class="flex-1 right-arrow">
                    <i class="iconfont" style="font-size:1.5em;">
                        &#xe62e;
                    </i>
                </div>
            </li>
        </ul>
    </div>
</template>

<style lang="scss" scoped>
    #my-vip {
        padding-top: 2em;
        background: #F0F1F5;
        min-height: 100vh;
        box-sizing: border-box;
        .top {
            width: 100%;
            height: 8em;
            background: #26a2ff;
            .card-amount {
                color: #fff;
                height: 100%;
                width: 100%;
                text-align: center;
                padding-top: 4.5em;
                h3 {
                    padding-top: 0.5em;
                    padding-bottom: 0.5em;
                }
            }
            .header-right {
                width: 100%;
                padding-right: 1em;
                height: 2em;
                box-sizing: border-box;
                .option-record {
                    color: #fff;
                }
            }
        }

        .tab-menu {
            width: 100%;
            height: 3em;
            border-top: 1px solid #ccc;
            >div {
                width: 50%;
                height: 100%;
                box-sizing: border-box;
            }

            .active {
                border-bottom: 0.2em solid #26a2ff;
                color: #26a2ff;
            }
        }


        .infos {
            height: 3em;
            background: #fff;
            padding: 0 1em;
            .icon{
                margin-right:0.2em;
            }
        }
        .card-list {
            padding: 0 0.6em;
            margin-top:1em;
            li {
                padding-left: 1em;
                padding-right: 1em;
                box-sizing: border-box;
                background: url('/images/vipBack.png') no-repeat;
                background-size:100% 100%;
                width: 100%;
                margin-bottom: 0.5em;
                .card-number {
                    height: 8em;
                    line-height: 13.5em;
                    font-size: 1em;
                    color: #000;
                }
                .card-type {
                    height: 5em;
                    line-height: 1.5em;
                    text-align: center;
                    margin: auto;
                    color: #fff;
                    .type {
                        em,
                        span {
                            font-weight: 700;
                            display: inline-block;
                        }
                        em {
                            font-size: 2em;
                            margin-bottom: 0.2em;
                        }
                        span {
                            font-size: 1em;
                        }
                    }

                }
                .right-arrow{
                    height: 3em;
                    line-height: 1.5em;  
                    color:#fff;
                }
            }
        }
    }
</style>

<script>
    import topBack from '../../components/topBack'
    import Loading from "../../utils/loading"
    import request from "../../utils/userRequest"
    import {Indicator,Toast} from 'mint-ui'

    export default {
        components: { topBack },
        data() {
            return {
                isBindVIP: false
            }
        },
        created () {
            this.init();
        },
        methods:{
            init(){
                Loading.getInstance().open("加载中...");
                request.getInstance.getData("api/promoter/cards_used_num")
                .then(res=>{
                    console.log(res);
                    Loading.getInstance().close();
                })
                .catch(err=>{
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                })
            },
            //开卡
            openCard(){
                this.$router.push('/vipCard/openCard');
            },
            //推广授权
            goGeneralize(){
                this.$router.push('/generalize')
            }
        }
    }
</script>