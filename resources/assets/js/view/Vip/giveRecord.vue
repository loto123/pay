<template>
    <div id="my-vip">
        <div class="top flex flex-v flex-align-center">
            <topBack style="background:#fff;" :title="'操作记录'" :backUrl="'\/index\/'">
            </topBack>
        </div>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="option-card flex flex-align-center flex-justify-center active">出卡记录</div>
            <div class="auth flex flex-align-center flex-justify-center" @click="goAuthRecord">授权记录</div>
        </div>
        <div class="giveCard-account flex flex-align-center">
            <div class="">已出卡({{cardNumber}})</div>
        </div>
        <div  class="card-container" ref='wrapper'>
            <ul class="card-list" v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
                <li class="list" v-for="(item,index) in cardList">
                    <div class="content-box flex flex-v flex-justify-around">
                        <div class="top-content flex flex-align-center">
                            <div class="openCard">
                                <img :src="item.type == 'binding'? '/images/openCard.png':'/images/giveCard.png'"/>
                            </div>
                            <div class="card-account">{{item.type == 'binding'?'绑定':'转让'}}账户<em>{{item.to_user}}</em></div>
                        </div>
                        <div class="bottom-content flex flex-justify-between">
                            <div class="card-number">NO.{{item.card_no}}</div>
                            <div class="time">{{item.type == 'binding'?'开卡':'转卡'}}时间:{{item.created_at}}</div>
                        </div>
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
        .card-list {
            padding: 0 0.6em;
            li {
                box-sizing: border-box;
                background: url('/images/vipBack.png') no-repeat;
                background-size: 100% 100%;
                margin-bottom: 0.5em;
                border-radius: 5px;
                padding:0 0.8em;
                .content-box {
                    height: 6em;
                    .top-content {
                        padding-top: 0.5em;
                        .openCard {
                            img {
                                width: 40px;
                            }
                        }
                        .card-account{
                            margin-left: 1.5em;
                            font-size: 1.1em;
                            color: #fff;
                            em{
                                margin-left:0.2em;
                            }
                        }
                    }
                    .bottom-content{
                        font-size: 0.7em;
                        .card-number {
                            color: #000;
                        }
                        .time{
                            color:#929292;
                        }
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
                cardList:[],
                cardNumber:null,     //出卡多少张

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
                request.getInstance().getData('api/promoter/cards-used',_data).then(res => {
                    this.cardList=res.data.data;
                    this.cardNumber=this.cardList.length;
                    Loading.getInstance().close();
                }).catch(err => {
                    Toast(err.data.msg);
                    Loading.getInstance().close();
                });
            },
            //查看授权记录
            goAuthRecord(){
                this.$router.push('/vipCard/authRecord');
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

					request.getInstance().getData('api/promoter/cards-used',_data).then(res => {

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