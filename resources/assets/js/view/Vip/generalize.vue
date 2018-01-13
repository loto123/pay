<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="color:#fff;background:#26a2ff;" :title="'VIP开卡'">
                <div class="flex flex-reverse flex-align-center header-right">
                    <a href="#" class="option-record">操作记录</a>
                </div>
            </topBack>
            <div class="card-amount">
                <div>{{used_cards}}</div>
                <h3>已出卡数(张)</h3>
            </div>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="flex flex-align-center flex-justify-center" @click="goOpenCard">开卡</div>
            <div class="flex flex-align-center flex-justify-center active">推广授权</div>
        </div>
        <div class="middle-content flex flex-align-center">
            <div class="input-wrap flex-7 flex flex-align-center flex-justify-center">
                <input type="text" v-model="searchMobile" placeholder="输入您要授权为推广员的账号">
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
            <div class="submit flex flex-justify-center" @click="authGener">
                <mt-button type="primary" size="large" style="width:90%;">授权为推广员</mt-button>
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
                    font-size: 1em;
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
    import { MessageBox, Toast } from 'mint-ui'

    export default {
        components: { topBack },
        data() {
            return {
                isBindVIP: false,
                used_cards:null,
                searchMobile:null,
                searchData: {                 // 搜索出来的数据
                    avatar: null,
                    user_id: null,
                    name: null
                }
            }
        },
        created(){
            this.init();
        },
        methods: {
            init(){
                Loading.getInstance().open("加载中...");
                request.getInstance().getData('api/promoter/cards_used_num')
                .then((res)=>{
                    this.used_cards=res.data.data.used_cards;
                    Loading.getInstance().close();
                })
                .catch((err) => {
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                })
            },
            //去主页
            goOpenCard() {
                this.$router.push('/vipCard')
            },
            // 搜索用户
            searchUser() {
                if (!this.searchMobile) {
                    Toast('请输入您要授权为推广员的账号');
                    return
                }

                var _data = {
                    user_id: this.searchUserMobile
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
            authGener() {
                var _data = {
                    user_id: this.searchUserMobile
                }
                const htmls = `
                    <div class="pop-content">
                        <div class="isunbind">确认给用户：17673181869为推广员？</div>
                    </div>
                    `;
                MessageBox.confirm('',{
                    message: htmls,
                    title: '确认信息',
                })
                .then(
                    () => {
                        request.getInstance().postData("api/promoter/grant", _data)
                            .then((res) => {
                                Toast('开通成功');
                            })
                            .catch((err) => {
                                Toast(err.data.msg);
                            })
                    },
                    () => {
                        //取消操作
                        console.log("已经取消");
                    }
                );
            }
        }
    }
</script>