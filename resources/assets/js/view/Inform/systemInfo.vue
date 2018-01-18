<template>
    <div id="inform">
        <topBack title="消息中心" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'">
            <div class="clear-inform flex flex-reverse flex-align-center" @click="del(3)">
                清空消息
            </div>
        </topBack>
        <div class="tab-menu flex flex-align-center flex-justify-center">
            <div class="flex flex-align-center flex-justify-center" @click="goShareBenefit">分润通知</div>
            <div class="flex flex-align-center flex-justify-center" @click="goUser">用户注册</div>
            <div class="flex flex-align-center flex-justify-center active">系统通知</div>
        </div>
        <div class="systemInfo-box" ref='wrapper'>
            <ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
                <li v-for="item in systemList" @click="goDetails(item.operator_state,item.notice_id,item.link)">
                    <div class="top-info flex flex-align-end flex-justify-between">
                        <div class="title flex-6">{{item.title}}</div>
                        <div class="date flex-4">{{item.created_at}}</div>
                    </div>
                    <div class="bottom-info flex flex-align-center flex-justify-between">
                        <div class="content flex-7" v-html="item.content"></div>
                        <div class="btn-wrap flex-3 flex flex-align-center flex-justify-around" v-if="item.operator_state==1">
                            <span v-for="(option,index) in item.operator_options" :style="{background: option.color}" @click="optionBtn(item.notice_id,index)">{{option.text}}</span>
                        </div>
                        <div v-if="item.operators_res.length != 0" class="status">{{item.operators_res.message}}</div>
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


<script>
    import request from '../../utils/userRequest';
    import topBack from "../../components/topBack.vue";
    import { MessageBox, Toast } from "mint-ui";
    import Loading from '../../utils/loading'

    export default {
        data() {
            return {
                systemList: [],

                wrapperHeight: null,
                loading: false,
                allLoaded: false,
                canLoading: true,
            };
        },
        created() {
            this.systemInfo();
        },
        components: { topBack },
        mounted(){
            this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
        },
        methods: {
            goShareBenefit() {
                this.$router.push("/inform");
            },
            goUser() {
                this.$router.push("/userRegister");
            },
            goDetails(status, e,links) { //详情
                if (status == 1) {
                    return
                }
                if (links != '') {
                    console.log(links);
                    location.href=links
                    return
                }
                this.$router.push("/systemInfo/system_Details" + "?notice_id=" + e);
                
                
            },
            systemInfo() { //列表
                var self = this;
                var _data = {
                    type:3,
                    limit:20,
                    offset :0
                }
                Loading.getInstance().open("加载中...");

                request.getInstance().getData('api/notice/index',_data)
                    .then((res) => {
                        self.systemList = res.data.data.list;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            //清空消息
            del(type) {
                MessageBox.confirm("是否确认清空全部消息?", "温馨提示").then(
                    () => {
                        request.getInstance().postData("api/notice/delete?type=" + type)
                            .then((res) => {
                                Toast({
                                    message: "清空成功",
                                    duration: 800
                                });
                                this.systemInfo();
                            })
                            .catch((err) => {
                                Toast(err.data.msg);
                            })
                    },
                    () => {
                        //取消操作
                    }
                );
            },
            optionBtn(noticeId, selectedValue) {
                Loading.getInstance().open("加载中...");
                var data = {
                    selected_value: selectedValue,
                    notice_id: noticeId
                }
                request.getInstance().postData('api/notice/operator', data)
                    .then((res) => {
                        console.log(res);
                        this.systemInfo();

                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            loadMore() {
                this.loading = false;
                if (this.systemList.length == 0 || !this.canLoading) {
                    return;
                }
                this.loading = true;
                this.canLoading = false;
                setTimeout(() => {

                    var _data = {
                        type:3,
                        limit: 20,
                        offset: [].concat(this.systemList).pop().notice_id
                    }

                    request.getInstance().getData('api/notice/index', _data).then(res => {

                        if (res.data.data.list.length == 0) {
                            this.canLoading = false;
                            this.loading = false;
                            return;
                        }

                        for (var i = 0; i < res.data.data.list.length; i++) {
                            this.systemList.push(res.data.data.list[i]);
                        }

                        this.canLoading = true;
                        this.loading = false;
                    }).catch(err => {

                    });
                }, 1500);

            }
        }
    };
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
    #inform {
        width: 100%;
        padding-top: 2em;
        box-sizing: border-box;
        height: 100vh;
        background: #f4f4f4;
        .clear-inform {
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            padding-right: 0.8em;
        }
    }

    .tab-menu {
        width: 100%;
        height: 3em;
        background: #fff;
        >div {
            width: 33.3%;
            height: 100%;
            box-sizing: border-box;
        }
        .active {
            border-bottom: 0.2em solid #26a2ff;
            color: #26a2ff;
        }
    }

    .systemInfo-box {
        li {
            border-bottom: 1px solid #ddd;
            padding: 0.5em 1em;
            .top-info {
                margin-bottom: 0.7em;
                .date {
                    color: #999;
                    font-size: 0.8em;
                    text-align: right;
                }
                .title {
                    color: #333;
                    font-size: 0.8em;
                }
            }
            .status {
                color: rgb(165, 59, 59);
                font-size: 0.7em;
            }
            .bottom-info {
                .content {
                    color: #999;
                    font-size: 0.8em;
                    text-overflow: ellipsis;
                    overflow: hidden;
                    white-space: nowrap;
                }
            }
            &:first-child {
                border-top: 1px solid #ddd;
            }
        }
    }

    .btn-wrap {
        width: 40%;
        height: 100%;
        >span {
            width: 40%;
            height: 70%;
            border-radius: 0.3em;
            text-align: center;
            line-height: 2em;
            font-size: 0.7em;
            color: #fff;
        }
    }
</style>