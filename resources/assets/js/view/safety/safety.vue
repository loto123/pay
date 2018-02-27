<template>
    <div id="safety">
        <!-- <div class="top flex flex-v flex-align-center">
            <topBack style="background:#F0F1F5;" :title="'安全'" :backUrl="'\/index\/'">
            </topBack>
        </div> -->
        <div class="header-container">
            <div class="number-box number1" v-if="show">
                <div class="pic-img">
                    <img src="/images/pic1.png">
                </div>
                <div class="number">{{users}}</div>
            </div>
            <div class="number-box number2" v-else>
                <div class="pic-img">
                    <img src="/images/pic2.png">
                </div>
                <div class="number">{{days}}</div>
            </div>
            <div class="header-bottom flex flex-justify-between flex-align-center">
                <div class="header-box">
                    <div class="img">
                        <img src="/images/safety.png" alt="">
                    </div>
                    <h2 class="title">更安全</h2>
                    <p class="content">
                        一直都100%兑付
                    </p>
                </div>
                <div class="header-box">
                    <div class="img">
                        <img src="/images/lucency.png" alt="">
                    </div>
                    <h2 class="title">更透明</h2>
                    <p class="content">
                        银行存管
                    </p>
                </div>
                <div class="header-box">
                    <div class="img">
                        <img src="/images/compliance.png" alt="">
                    </div>
                    <h2 class="title">更合规</h2>
                    <p class="content">
                        权威律所鉴定
                    </p>
                </div>
            </div>
        </div>
        <div class="userData-container">
            <h2>用户数据信息安全保障</h2>
            <ul class="userData-box">
                <li class="flex flex-align-center">
                    <div class="img">
                        <img src="/images/police.png">
                    </div>
                    <div class="data-content">
                        <h3>公安部门的认可</h3>
                        <p>通过公安部严格的信息系统安全保护备案</p>
                    </div>
                </li>
                <li class="flex flex-align-center">
                    <div class="img">
                        <img src="/images/bankSafety.png">
                    </div>
                    <div class="data-content">
                        <h3>银行安全级别</h3>
                        <p>采用与银行相同级别的数据安全保障体系，保障用户信息安全</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="privacy-container">
            <h2>用户隐私安全保障</h2>
            <div class="privacy-box flex flex-align-center">
                <div class="img">
                    <img src="/images/privacy.png">
                </div>
                <div class="privacy-content">
                    <h3>隐私安全</h3>
                    <ul class="list">
                        <li class="flex"><i></i><span>严格的用户信息操作规范</span></li>
                        <li class="flex"><i></i><span>严格遵守相关法规</span></li>
                        <li class="flex"><i></i><span>与第三方机构合作时，要求遵守相关协议</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    #safety {
        /* padding-top: 2em; */
        background: #F0F1F5;
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
    }

    .number-box {
        position: relative;
        .pic-img {
            img {
                display: block;
                width: 100%;
            }
        }
        .number {
            position: absolute;
            left: 1.5em;
            top: 3.2em;
            color: #fff;
            font-size: 1.3em;
        }
    }

    .header-container {
        background: #fff;
        .header-bottom {
            padding: 2em 1em;
            .header-box {
                text-align: center;
                img {
                    display: block;
                    width: 40px;
                    margin: auto;
                    margin-bottom: 0.5em;
                }
                .title {
                    font-size: 0.8em;
                    color: #000;
                    margin-bottom: 0.5em;
                }
                .content {
                    font-size: 0.8em;
                    color: #a9a9a9;
                }
            }
        }
    }

    .userData-container {
        background: #fff;
        margin-top: 1em;
        padding: 1em;
        h2 {
            margin-left: 75px;
            font-size: 1.2em;
        }
        .userData-box {
            li {
                margin-top: 1.5em;
                height: 4em;
                .img {
                    width: 65px;
                    img {
                        display: block;
                        width: 65px;
                    }
                }

                .data-content {
                    margin-left: 10px;
                    h3 {
                        margin-bottom: 0.5em;
                        color: #262626;
                    }
                    p {
                        font-size: 0.9em;
                        color: #a9a9a9;
                        line-height: 1.5em;
                    }
                }
            }
        }
    }

    .privacy-container {
        background: #fff;
        margin-top: 1em;
        padding: 1em;
        h2 {
            font-size: 1.2em;
            margin-left: 75px;
        }
        .privacy-box {
            margin-top: 1em;
            img {
                display: block;
                width: 65px;
            }
        }
        .privacy-content {
            margin-left: 10px;
            h3 {
                font-size: 1em;
                margin-bottom: 0.5em;
            }
            .list {
                li {
                    color: #a9a9a9;
                    font-size: 0.9em;
                    margin-bottom: 0.4em;
                    i{  
                        display: block;
                        width: 8px;
                        height: 8px;
                        background: #aaa;
                        border-radius: 50%;
                        margin-right: 3px;
                        margin-top: 3px;
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
    import { MessageBox, Toast } from 'mint-ui'

    export default {
        components: { topBack },
        data() {
            return {
                users: null,
                days: null,
                show: true
            }
        },
        created() {
            this.init();
            this.shows();
        },
        methods: {
            init() {
                Loading.getInstance().open();
                request.getInstance().getData("api/index/safe").then(res => {
                    this.users = res.data.data.users;
                    this.days = res.data.data.days;
                    Loading.getInstance().close();
                }).catch(err => {
                    Loading.getInstance().close();
                });
            },
            shows() {
                setInterval(() => {
                    this.show = !this.show;
                }, 3000)
            }
        }
    }
</script>