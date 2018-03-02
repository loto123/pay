<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="background:#fff;" :title="'操作记录'" :backUrl="'\/index\/'">
            </topBack>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="option-card flex flex-align-center flex-justify-center" @click="goGiveRecord">出卡记录</div>
            <div class="auth flex flex-align-center flex-justify-center active">授权记录</div>
        </div>
        <div class="giveCard-account flex flex-align-center">
            <div class="">已成功授权({{personalNumber}}人)</div>
        </div>
        <div class="auth-container" ref='wrapper'>
            <ul class="auth-list" v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
                <li class="list flex flex-align-start" v-for="(item,index) in authList">
                    <div class="personal-img">
                        <img :src="item.avatar">
                    </div>
                    <div class="auth-content">
                        <div class="personal">{{item.name}}</div>
                        <div class="account">账号:{{item.user_id}}</div>
                        <div class="time">授权时间:{{item.created_at}}</div>
                    </div>
                </li>
            </ul>
            <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
                <mt-spinner type="fading-circle"></mt-spinner>
                <span style="margin-left: 0.5em;color:#999;">加载中...</span>
            </p>
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
        .giveCard-account{
            padding: 0 0.6em;
            height: 4em;
            color: #000;
        }
        .auth-list {
            padding-left:0.6em;
            background: #fff;
            li {
                box-sizing: border-box;
                padding:0.9em 0.8em;
                border-bottom:1px solid #DCDCDC;
                .personal-img{
                    img{
                        width: 60px;
                        height: 60px;
                        border-radius:50%;
                    }
                }
                &:last-child{
                    border-bottom:none;  
                }
                .auth-content{
                    margin-left:0.8em;
                    font-size: 0.9em;
                    .personal,.account{
                        margin-bottom:0.6em;
                    }
                    .time{
                        color: #929292;
                    }
                    em{
                        margin-left:0.3em;
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
                authList:[],
                personalNumber:null,     //出卡多少张

                wrapperHeight: null,
                loading: false,
                allLoaded: false,
                canLoading: true
            }
        },
        created() {
            this.init();
        },
        mounted(){
            this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
        },
        methods: {
            init() {
                var _data = {
                    limit: 15,
                    offset: 0
                }
                Loading.getInstance().open("加载中...");
                request.getInstance().getData('api/promoter/grant-history',_data).then(res => {
                    this.authList=res.data.data;
                    this.personalNumber=this.authList.length;
                    Loading.getInstance().close();
                }).catch(err => {
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                });
            },
            //查看出卡记录
            goGiveRecord(){
                this.$router.push('/vipCard/giveRecord');
            },
            loadMore() {
				this.loading = false;
				if (this.cardList.length == 0 || !this.canLoading) {
					return;
				}
				this.loading = true;
				this.canLoading = false;
				setTimeout(() => {

					var _data = {
						limit: 15,
						offset: [].concat(this.cardList).pop().id
					}

					request.getInstance().getData('api/promoter/grant-history',_data).then(res => {

						if (res.data.data.list.length == 0) {
							this.canLoading = false;
							this.loading = false;
							return;
						}

						for (var i = 0; i < res.data.data.list.length; i++) {
							this.cardList.push(res.data.data.list[i]);
						}

						this.canLoading = true;
						this.loading = false;
					}).catch(err => {

					});
				}, 1500);

			}
        }
    }
</script>