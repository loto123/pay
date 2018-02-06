<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="color:#fff;background:#26a2ff;" :title="'VIP开卡'" :backUrl="'\/index\/'">
                <div class="flex flex-reverse flex-align-center header-right" @click="checkRecord">
                    <a href="javascript:;" class="option-record">操作记录</a>
                </div>
            </topBack>
            <div class="card-amount">
                <div>{{used_cards}}</div>
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
            <div class="flex-3">我的VIP卡({{cardNumber}}张)</div>
        </div>

        <ul class="card-list">
            <li class="list" v-for="item in cardList" @click="openCard(item.id)">
                <card :cardName="item.card_name" :percent="item.percent" :cardNumber="item.card_no" style="height:6em;">
                    <i class="iconfont" style="font-size:1.5em;">
                        &#xe62e;
                    </i>
                </card>
            </li>
        </ul>
        <passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
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
            background: #fff;
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
            height: 4em;
            padding: 0 1em;
            .icon {
                margin-right: 0.2em;
            }
        }
        .card-list {
            padding: 0 0.6em;
            li {
                box-sizing: border-box;
                background: url('/images/vipBack.png') no-repeat;
                background-size: 100% 100%;
                width: 100%;
                margin-bottom: 0.5em;
                padding: 0 0.6em;
                .bottom-content{
                    height: 30% !important;
                }
            }
        }
    }
</style>

<script>
    import topBack from '../../components/topBack'
    import card from '../../components/card'
    import Loading from "../../utils/loading"
    import request from "../../utils/userRequest"
    import passWorld from "../../components/password"
    import { Toast } from 'mint-ui'

    export default {
        components: { topBack, passWorld, card },
        data() {
            return {
                showPasswordTag: false,       // 密码弹出开关
                isBindVIP: false,
                used_cards: null,
                cardList: [],
                curCard_id: null,
                cardNumber: null      //多少张卡
            }
        },
        created() {
            this.init();
        },
        methods: {
            hidePassword() {
                this.showPasswordTag = false;
            },
            //支付密码验证
            callBack(password) {
                var temp = {};
                temp.password = password;
                request.getInstance().postData('api/my/pay_password', temp)
                    .then((res) => {
                        this.$router.push('/vipCard/openCard?card_id=' + this.curCard_id);
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                    })
            },
            init() {
                Loading.getInstance().open("加载中...");
                Promise.all([request.getInstance().getData('api/promoter/cards_used_num'), request.getInstance().getData('api/promoter/cards-reserve')])
                    .then((res) => {
                        this.used_cards = res[0].data.data.used_cards;
                        this.cardList = res[1].data.data;
                        this.cardNumber = this.cardList.length;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            //开卡
            openCard(card_id) {
                request.getInstance().getData('api/my/info')
					.then((res) => {
						if (res.data.data.has_pay_password == 0) {
							//调转到设置支付密码
							this.$router.push('/my/setting_password');
						} else {
							this.curCard_id = card_id;
                            this.showPasswordTag = true;
						}
					})
					.catch((err) => {
						Toast(err.data.msg);
					})
            },
            //推广授权
            goGeneralize() {
                this.$router.push('/generalize')
            },
            //查看记录
            checkRecord() {
                this.$router.push('/vipCard/giveRecord')
            }
        }
    }
</script>