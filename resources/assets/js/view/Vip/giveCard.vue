<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="background:#fff;" :title="'VIP开卡'" :backUrl="'\/index\/'">
            </topBack>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="flex flex-align-center flex-justify-center" @click="openCard">开卡</div>
            <div class="flex flex-align-center flex-justify-center active">转卡给推广员</div>
        </div>
        <ul class="card-list">
            <li class="list">
                <card :cardName="card_name" :percent="percent" :cardNumber="card_no" style="height:10em;">
                    <div class="openCard">
                        <img src="/images/giveCard.png">
                    </div>
                </card>
            </li>
        </ul>
        <div class="flex flex-justify-center" style="margin:0.5em 0;">
            <i class="iconfont" style="font-size:3em;color:#E2CD8F;">
                &#xe699;
            </i>
        </div>
        <div class="middle-content flex flex-align-center">
            <div class="input-wrap flex-7 flex flex-align-center flex-justify-center">
                <input type="text" v-model="searchMobile" placeholder="输入您要转卡的推广员账号">
            </div>
            <div class="search-btn flex-3 flex flex-align-center flex-justify-center" @click="searchUser">
                搜索
            </div>
        </div>
        <div class="search-result" v-if="searchData.user_id">
            <div class="user-info flex flex-align-center flex-justify-center">
                <div class="info">
                    <div class="info-wrap">
                        <img :src="searchData.avatar">
                    </div>
                    <div class="info-right">
                        <span style="margin-top:0.5em;">{{searchData.name}}</span>
                        <span>账号:{{searchData.user_id}}</span>
                    </div>
                </div>
            </div>
            <div class="submit flex flex-justify-center" @click="giveCard">
                <mt-button type="primary" size="large" style="width:90%;">转卡</mt-button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    #my-vip {
        padding-top: 2em;
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

        .tab-menu {
            width: 100%;
            height: 3em;
            margin-bottom: 1em;
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
        .card-list {
            padding: 0 0.6em;
            li {
                padding: 0 0.6em;
                box-sizing: border-box;
                background: url('/images/vipBack2.png') no-repeat;
                background-size: 100% 100%;
                width: 100%;
                border-radius: 5px;
                .openCard {
                    margin-top: -2em;
                    img {
                        width: 40px;
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
            background: #fff;
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
                    font-size: 1em;
                }
            }

            .search-btn {
                width: 100%;
                height: 100%;
                border: 1px solid #26a2ff;
                border-right: none;
                background: #26a2ff;
                color:#fff;
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
    import card from '../../components/card'
    import Loading from "../../utils/loading"
    import request from "../../utils/userRequest"
    import { MessageBox, Toast } from 'mint-ui'

    export default {
        components: { topBack,card },
        data() {
            return {
                isBindVIP: false,
                searchMobile: null,
                searchData: {                 // 搜索出来的数据
                    avatar: null,
                    user_id: null,
                    name: null
                },
                card_name:null,     //卡的名字
                card_no:null,       //卡号
                percent:null,        //分润比例
                card_id:null
            }
        },
        created(){
            this.init();
        },
        methods: {
            init(){
                var data={
                    card_id:this.$route.query.card_id
                }
                Loading.getInstance().open();
                request.getInstance().postData('api/promoter/card-detail',data).then(res => {
                    this.card_name = res.data.data.card_name;
                    this.card_no = res.data.data.card_no;
                    this.percent = res.data.data.percent;
                    Loading.getInstance().close();
                }).catch(err => {
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                });
            },
            openCard() {
                this.card_id=this.$route.query.card_id;
                this.$router.push('/vipCard/openCard?card_id='+this.card_id);
            },
            // 搜索用户
            searchUser() {
                if (!this.searchMobile) {
                    Toast('请输入您要开卡的账号');
                    return
                }

                var _data = {
                    user_id: this.searchMobile
                }
                Loading.getInstance().open();
                request.getInstance().postData('api/promoter/query-promoter', _data).then(res => {
                    this.searchData = res.data.data;
                    Loading.getInstance().close();
                }).catch(err => {
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                });
            },
            //开通vip
            giveCard() {
                var _data = {
                    card_no: this.card_no,
                    user_id: this.searchMobile
                }
                const htmls = `
                    <div class="pop-content">
                        <div class="isunbind">确认转让卡给用户：`+this.searchMobile+`？</div>
                        <div class="notice">(转卡成功后不可撤回)</div>
                    </div>
                    `;
                MessageBox.confirm('', {
                    message: htmls,
                    title: '确认信息',
                }).then(
                    () => {
                        request.getInstance().postData("api/promoter/transfer-card", _data)
                            .then((res) => {
                                Toast('转卡成功');
                                this.$router.push('/vipCard');
                            })
                            .catch((err) => {
                                Toast(err.data.msg);
                            })
                    },() => {
                        //取消操作
                    }
               );
            }
        }
    }
</script>