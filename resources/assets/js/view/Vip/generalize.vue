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
            <div class="flex flex-align-center flex-justify-center" @click="goOpenCard">开卡</div>
            <div class="flex flex-align-center flex-justify-center active">推广授权</div>
        </div>
        <div class="middle-content flex flex-align-center">
            <div class="input-wrap flex-7 flex flex-align-center flex-justify-center">
                <input type="text" v-model="searchUserMobile" @click="searchInput" v-on:blur="inputBlur" placeholder="输入您要开卡的账号">
            </div>

            <div class="search-btn flex-3 flex flex-align-center flex-justify-center" @click="searchUser">
                搜索
            </div>
        </div>
        <div class="search-result">
            <div class="user-info flex flex-align-center flex-justify-center">
                <div class="info">
                    <div class="info-wrap">
                        <img src="/images/avatar.jpg">
                    </div>
                    <div class="info-right">
                        <span style="margin-top:0.5em;">loto</span>
                        <span>账号:231321</span>
                    </div>
                </div>
            </div>
            <div class="submit flex flex-justify-center" @click="submit">
                <mt-button type="primary" size="large" style="width:90%;">开通VIP</mt-button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    #my-vip {
        padding-top: 2em;
        background: #fff;
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
            margin-bottom: 1em;
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
        .middle-content {
            width: 94%;
            height: 3em;
            box-sizing: border-box;
            border: 1px solid #bbb;
            margin: auto;
            .input-wrap {
                width: 100%;
                height: 3em;
                box-sizing: border-box;
                >input {
                    display: block;
                    outline: none;
                    border: none;
                    height: 75%;
                    width: 85%;
                    text-indent: 2em;
                    font-size: 1.1em;
                }
            }

            .search-btn {
                width: 100%;
                height: 100%;
                border: 1px solid #bbb;
                border-right: none;
            }
        }
        .user-info {
            width: 100%;
            text-align: center;
            margin: 2em 0 3em 0;
            .info {
                width: 100%;
                .info-wrap {
                    >img {
                        width: 4em;
                        height: 4em;
                        border-radius: 50%;
                    }
                }

                .info-right {
                    >span {
                        margin-top: 0.5em;
                        display: block;
                        width: 100%;
                    }
                }
            }
        }
    }
</style>

<script>
    import topBack from '../../components/topBack'
    import Loading from "../../utils/loading"
    import request from "../../utils/userRequest"
    import { Indicator, Toast } from 'mint-ui'

    export default {
        components: { topBack },
        data() {
            return {
                isBindVIP: false
            }
        },
        methods: {
            //去主页
            goOpenCard() {
                this.$router.push('/vipCard')
            }
        }
    }
</script>