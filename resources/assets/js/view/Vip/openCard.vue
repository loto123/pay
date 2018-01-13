<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="background:#fff;" :title="'VIP开卡'">
                <div class="flex flex-reverse flex-align-center header-right">
                    <a href="#" class="option-record">操作记录</a>
                </div>
            </topBack>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="option-card flex flex-align-center flex-justify-center active">开卡</div>
            <div class="auth flex flex-align-center flex-justify-center" @click="giveCard">转卡给推广员</div>
        </div>
        <ul class="card-list">
            <li class="flex flex-align-center">
                <div class="flex-3 card-number">NO.123456</div>
                <div class="flex-5 card-type">
                    <div class="type">
                        <em>VIP</em>
                        <span>金卡</span>
                    </div>
                    <div class="share-profit">尊享分润比例：5‰</div>
                </div>
                <div class="flex-2">
                    开卡
                </div>
            </li>
        </ul>
        <div class="flex flex-justify-center" style="margin:0.7em 0;">
            <i class="iconfont" style="font-size:2em;color:#E2CD8F;">
                &#xe627;
            </i>
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
            .header-right {
                width: 100%;
                padding-right: 1em;
                height: 2em;
                box-sizing: border-box;
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
        .card-list {
            padding: 0 0.6em;
            li {
                padding-left: 1em;
                padding-right: 1em;
                box-sizing: border-box;
                height: 10em;
                background: #E2CD8F;
                width: 100%;
                margin-bottom: 0.5em;
                color: #fff;
                border-radius: 5px;
                .card-number {
                    height: 8em;
                    line-height: 14em;
                    font-weight: 700;
                    font-size: 1em;
                }
                .card-type {
                    text-align: center;
                    margin: auto;
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
            //转卡给推广员
            giveCard() {
                this.$router.push('/vipCard/giveCard')
            }
        }
    }
</script>