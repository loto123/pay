<template>
    <div id="shop-detail" v-if="isShow">
        <topBack style="color:#fff; background:#26a2ff;"></topBack>

        <div class="top flex flex-v flex-align-center">
            <div class="img-wrap flex flex-justify-center flex-align-center flex-wrap-on">
                <img :src="logo" alt="" class="avatar">
            </div>
            <h3 style="margin-top:0.5em;">{{shopName}}</h3>
            <h3>公会id:{{shopId}}</h3>
        </div>

        <div class="menu flex " v-if="isGroupMaster">
            <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goShopAccount">
                <i class="iconfont">
                    &#xe61e;
                </i>
                <h3>公会账户</h3>
            </div>

            <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goDealManagement">
                <i class="iconfont">
                    &#xe63b;
                </i>
                <h3>任务管理</h3>
            </div>
            <!-- <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goShopOrder">
              <i class="iconfont">
                  &#xe603;
              </i>
              <h3>
                  公会订单
              </h3>
          </div> -->
        </div>

        <div class="shop-info">

            <div class="info-item flex flex-align-center flex-justify-between" @click="updateShop('shopName')">
                <span class="title flex-4"> 公会名称 </span>
                <span class="name flex-5">{{SetString(shopName,16)}}</span>
                <i class="iconfont flex-1">
                    &#xe62e;
                </i>
            </div>

            <!-- <div class="shop-qrcode flex flex-align-center flex-justify-between" @click="invite">
            <span class="title flex-8">公会二维码</span>
            <span class="qr-code flex-1">
                <i class="iconfont">
                    &#xe9c4;
                </i>
            </span>
            <i class="iconfont flex-1">
            &#xe62e;
            </i>
        </div> -->
        </div>

        <!-- 公会成员 -->
        <div class="member-wrap flex flex-align-center flex-justify-around" @click="goMember">

            <div class="flex-1" style="padding-left:1em;">
                <i class="iconfont icon">
                    &#xe73c;
                </i>
            </div>

            <div class="avatar-wrap flex-5 flex flex-justify-around">

                <div class="avatar-item" v-for="(item,index) in membersList">
                    <img :src="item.avatar" alt="" v-if="index < 5">
                </div>

                <div class="add-avatar flex flex-align-center flex-justify-center" @click.stop="addMember" v-if="isGroupMaster">
                    <i class="iconfont">
                        &#xe600;
                    </i>
                </div>
            </div>

            <div class="flex-4 flex flex-reverse">

                <i class="iconfont" style="padding-right:1em;">
                    &#xe62e;
                </i>
                <span style="color:#666;">{{membersCount}}名成员</span>
            </div>

        </div>

        <div class="invite-wrap">
            <div class="flex flex-align-center flex-justify-between" @click="invite">
                <span class="title flex-9"> 邀请新会员 </span>
                <i class="iconfont flex-1">
                    &#xe62e;
                </i>
            </div>

            <div class="invite-link-switch flex flex-align-center flex-justify-between" v-if="isGroupMaster">
                <span class="title flex-9"> 邀请链接 </span>
                <span class="text flex-1 flex flex-reverse">
                    <mt-switch v-model="inviteLinkStatus" @change="changeInviteLinkStatus"></mt-switch>
                </span>
            </div>
        </div>

        <div class="platform">
            <div class="flex flex-align-center flex-justify-between" v-if="isGroupMaster">
                <span class="title flex-9"> 平台交易费 </span>
                <span class="text flex-1">{{platform_fee}}%</span>
            </div>

            <div class="flex flex-align-center flex-justify-between" @click="updateShop('rate')">
                <span class="title flex-9"> 任务默认倍率 </span>
                <span class="text flex-1">{{rate}}</span>
            </div>

        </div>

        <div class="commission" v-if="isGroupMaster">
            <div class="flex flex-align-center flex-justify-between">
                <span class="title flex-9" @click="updateShop('percent')"> 公会佣金费率 </span>
                <span class="text flex-1">{{percent}}%</span>
            </div>

            <div class="flex flex-align-center flex-justify-between">
                <span class="title flex-9"> 是否开启任务功能 </span>
                <span class="text flex-1 flex flex-reverse">
                    <mt-switch v-model="tradeStatus" @change="changeTradeStatus"></mt-switch>
                </span>
            </div>
        </div>

        <div class="complaint" v-if="!isGroupMaster && user_feedback" @click="complaint">
            <div class="flex flex-align-center flex-justify-between">
                <span class="title flex-9"> 投诉 </span>
                <span class="text flex-1"></span>
            </div>
        </div>

        <div class="button-wrap">
            <mt-button type="danger" size="large" @click="dissShop" v-if="isGroupMaster">解散公会</mt-button>
            <mt-button type="danger" size="large" @click="exitShop" v-if="!isGroupMaster">退出公会</mt-button>
        </div>

        <div class="add-members-pop flex flex-justify-center flex-align-center" @touchmove.prevent v-if="addMemberSwitch" v-bind:class="{poAbsolute:isFixed}">
            <div class="content-tab">

                <div class="top-content flex flex-align-center">
                    <span class="flex-2"></span>
                    <h3 class="flex-9">邀请新会员</h3>
                    <span class="flex-2" @click="closeMemberTab">
                        <i class="iconfont" style="padding:0.5em;border:1px solid #888; border-radius:50%;color:#888;"> &#xe60a;</i>
                    </span>
                </div>

                <div class="middle-content flex flex-align-center">
                    <div class="input-wrap flex-7 flex flex-align-center flex-justify-center">
                        <input type="text" v-model="searchUserMobile" @click="searchInput" v-on:blur="inputBlur" placeholder="点击搜索好友">
                    </div>

                    <div class="search-btn flex-3 flex flex-align-center flex-justify-center" @click="searchUser">
                        搜索
                    </div>
                </div>

                <div class="user-info flex flex-align-center flex-justify-center" v-if="searchData.id">
                    <div class="info flex flex-1">
                        <div class="info-wrap flex flex-align-center flex-3 flex-justify-center">
                            <img :src="searchData.avatar" alt="">
                        </div>

                        <div class="info-right flex-4 flex flex-v flex-align-center flex-justify-center">
                            <span style="margin-top:-0.5em;">昵称:{{searchData.name}}</span>
                            <span>账号:{{searchData.mobile}}</span>
                        </div>
                    </div>
                </div>

                <div class="no-result" v-if="searchData.id!=0 && !searchData.id">
                    <h3>无匹配结果</h3>
                </div>

                <div class="submit flex flex-justify-center" v-if="searchData.id" @click="submitAddMember">
                    <mt-button type="default" size="large" style="width:70%;">邀请</mt-button>
                </div>

            </div>
        </div>

    </div>
</template>

<style lang="scss" scoped>
    #shop-detail {
        .poAbsolute {
            position: absolute !important;
        }

        background: #eee;
        min-height: 100vh;

        .top {
            padding-top: 2em;
            /*height: 10em;*/
            background: #26a2ff;
            box-sizing: border-box;

            .img-wrap {
                width: 4.5em;
                height: 4.5em;
                background: #eee;
                border-radius: 0.3em;
                margin-top: 0.5em;
                padding: 0.2em;

                .avatar {
                    margin-top: 1%;
                    margin-left: 1%;
                    width: 100%;
                    height: 100%;
                }
            }

            h3 {
                padding-top: 0.2em;
                padding-bottom: 0.2em;
                color: #fff;
                font-size: 0.9em;
            }
        }

        .menu {
            height: 6em;
            background: #fff;

            .menu-item {
                width: 25%;
                height: 100%;
                box-sizing: border-box;
                padding-top: 0.4em;

                >i {
                    display: block;
                    font-size: 2.8em;
                    color: #555;
                }

                h3 {
                    font-size: 0.9em;
                }
            }
        }

        .shop-info {
            margin-top: 0.5em;
            background: #fff;
            /* height: 5em; */
            width: 100%;

            .info-item {
                height: 2.5em;
                width: 100%;
                border-bottom: 0.05em solid #eee;

                .title {
                    box-sizing: border-box;
                    padding-left: 1em;
                }

                >i {
                    box-sizing: border-box;
                    padding-right: 1em;
                    text-align: right;
                }
            }

            .shop-qrcode {
                height: 2.5em;

                .title {
                    box-sizing: border-box;
                    padding-left: 1em;
                }

                >i {
                    box-sizing: border-box;
                    padding-right: 1em;
                    text-align: right;
                }

                .qr-code {
                    text-align: right;
                    >i {
                        font-size: 1.2em;
                        color: #555;
                    }
                }
            }
        }

        .member-wrap {
            margin-top: 0.5em;
            width: 100%;
            height: 4em;
            background: #fff;

            .avatar-wrap {
                .avatar-item {
                    // width:
                    >img {
                        display: block;
                        width: 2.3em;
                        border-radius: 0.4em;
                        height: 2.3em;
                        margin-left: 0.2em;
                    }
                }

                .add-avatar {
                    box-sizing: border-box;
                    width: 2.3em;
                    border-radius: 0.4em;
                    height: 2.3em;
                    border: 0.1em solid #ccc;
                    margin-left: 0.2em;

                    >i {
                        font-size: 2em;
                        color: #ccc;
                    }
                }
            }

            .icon {
                font-size: 2em;
            }
        }

        .invite-wrap {
            width: 100%; // height: 5em;
            background: #fff;
            margin-top: 0.5em;

            >div {
                height: 2.5em;
                padding-left: 1em;
                box-sizing: border-box;
                &:nth-child(1) {
                    border-bottom: 0.05em solid #eee;
                }
                i {
                    text-align: right;
                    padding-right: 1em;
                }
            }

            .invite-link-switch {
                .text {
                    padding-right: 1em;
                }
            }
        }

        .platform {
            width: 100%; // height: 5em;
            background: #fff;
            margin-top: 0.5em;

            >div {
                &:nth-child(1) {
                    border-bottom: 0.05em solid #eee;
                }
                box-sizing: border-box;
                height: 2.5em;
                padding-left: 1em;

                .text {
                    text-align: right;
                    padding-right: 1em;
                    color: #555;
                }
            }
        }

        .commission {
            width: 100%;
            background: #fff;
            margin-top: 0.5em;

            >div {
                &:nth-child(1) {
                    border-bottom: 0.05em solid #eee;
                }
                box-sizing: border-box;
                height: 2.5em;
                padding-left: 1em;

                .text {
                    text-align: right;
                    padding-right: 1em;
                    color: #555;
                }
            }
        }

        .complaint {
            @extend .commission;
        }

        .button-wrap {
            width: 90%;
            margin: 0 auto;
            margin-top: 1em;
            padding-bottom: 1em;
        }

        .add-members-pop {
            width: 100%;
            height: 100vh;
            /*position: absolute;*/
            position: fixed;
            background: rgba(0, 0, 0, 0.7);
            top: 0em;
            left: 0em;

            .content-tab {
                width: 90%;
                height: 16em;
                background: #fff;
                border-radius: 1em;

                .top-content {
                    width: 100%;
                    height: 3em;

                    >h3 {
                        text-align: center;
                    }
                }

                .middle-content {
                    width: 100%;
                    height: 3em;
                    box-sizing: border-box;
                    border: 1px solid #bbb;

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
                    }
                }

                .user-info {
                    height: 6em;
                    width: 100%;

                    .info {
                        width: 90%;
                        height: 6em;

                        .info-wrap {
                            >img {
                                width: 4em;
                                height: 4em;
                                border-radius: 0.4em;
                            }
                        }

                        .info-right {
                            >span {
                                margin-top: 1em;
                                display: block;
                                width: 100%;
                                text-align: left;
                            }
                        }



                    }
                }

                .no-result {
                    h3 {
                        text-align: center;
                        font-size: 1.5em;
                        height: 4em;
                        line-height: 4em;
                        color: #555;
                    }
                }

                .submit {
                    width: 100%;
                }
            }
        }
    }
</style>

<script>
    import topBack from "../../components/topBack";
    import { Toast, MessageBox } from "mint-ui";
    import request from "../../utils/userRequest";
    import utils from "../../utils/utils"
    import Loading from "../../utils/loading";

    export default {
        name: "shopDetail",
        beforeMount() { },
        created() {
            this.init();
        },
        mounted() { },
        components: { topBack },
        data() {
            return {
                isShow: false,

                inviteLinkStatus: true,     // 邀请链接状态
                tradeStatus: true,          // 任务状态
                isGroupMaster: true,        // 是否是群主
                searchUserMobile: null,      // 搜索公会成员的手机号

                isFixed: false,

                shopId: null,
                shopName: null,
                rate: null,
                percent: null,
                membersCount: null,
                membersList: [],
                active: null,
                platform_fee: null,
                addMemberSwitch: false,       // 添加成员开关
                logo: null,                    // 公会的头像
                fee_limit:0,

                searchData: {                  // 搜索出来的数据
                    avatar: null,
                    id: 0,
                    mobile: null,
                    name: null
                },
                user_feedback: null            // 投诉配置的地址
            };
        },
        methods: {
            // 跳转控制
            hide() { },
            goMember() {
                if (!this.membersCount) {
                    Toast("当前公会无成员,");
                    return;
                }
                this.$router.push("/shop/shop_member?shopId=" + this.shopId + "&isGroupMaster=" + this.isGroupMaster);
            },
            goDealManagement() {
                this.$router.push("/shop/deal_management?shopId=" + this.shopId);
            },
            goShopAccount() {
                this.$router.push("/shop/shopAccount?id=" + this.shopId);
            },
            goShopOrder() {
                this.$router.push("/shop/shopOrder");
            },

            invite() {
                this.$router.push("/shop/shopShare?id=" + this.shopId);
            },

            addMember() {
                this.openMemberTab();
            },

            // 发送邀请用户请求
            submitAddMember() {
                Loading.getInstance().open();
                var _data = {
                    shop_id: this.shopId,
                    user_id: this.searchUserMobile
                }

                request.getInstance().postData("api/shop/invite/" + this.shopId + "/" + this.searchData.id).then(res => {
                    Loading.getInstance().close();
                    Toast("邀请用户成功");
                    this.closeMemberTab();
                }).catch(err => {
                    Loading.getInstance().close();
                    Toast(err.data.msg);
                });
            },

            // 数据控制
            init() {
                Loading.getInstance().open();

                this.user_feedback = window.user_feedback;

                var self = this;
                var _id = this.$route.query.id;

                request
                    .getInstance()
                    .getData("api/shop/detail/" + _id)
                    .then(res => {
                        this.isGroupMaster = res.data.data.is_manager;
                        this.shopId = res.data.data.id;
                        this.shopName = res.data.data.name;
                        this.rate = res.data.data.rate;
                        this.platform_fee = res.data.data.platform_fee;
                        if (this.isGroupMaster) {
                            this.percent = res.data.data.percent;
                        }
                        this.membersCount = res.data.data.members_count;
                        this.membersList = res.data.data.members;
                        this.logo = res.data.data.logo;
                        this.fee_limit = res.data.data.guild_commission;

                        if (res.data.data.active == 1) {
                            this.tradeStatus = true;
                        } else {
                            this.tradeStatus = false;
                        }

                        if (res.data.data.use_link == 1) {
                            this.inviteLinkStatus = true;
                        } else {
                            this.inviteLinkStatus = false;
                        }

                        Loading.getInstance().close();

                        this.isShow = true;

                    })
                    .catch(error => {
                        Toast(error.data.msg);
                        this.$router.go(-1);
                    });
            },

            // 解散公会
            dissShop() {
                MessageBox.confirm('确定删除公会?').then(action => {

                    if(this.tradeStatus == true){
                        Toast("请先关闭公会交易功能");
                        return;
                    }

                    Loading.getInstance().open();

                    request
                        .getInstance()
                        .postData("api/shop/close/" + this.shopId)
                        .then(res => {
                            Loading.getInstance().close();
                            Toast("公会解散成功");
                            this.$router.push("/shop");
                        })
                        .catch(error => {
                            Loading.getInstance().close();
                            Toast(error.data.msg);
                        });
                }).catch(err => {

                });

            },

            exitShop() {
                MessageBox.confirm('确定退出公会?').then(action => {

                    Loading.getInstance().open();

                    request
                        .getInstance()
                        .postData("api/shop/quit/" + this.shopId)
                        .then(res => {
                            Loading.getInstance().close();
                            Toast("退出公会成功");
                            this.$router.push("/shop");
                        })
                        .catch(error => {
                            console.error(error);
                        });
                }).catch(err => {

                });

            },

            closeMemberTab() {
                this.addMemberSwitch = false;
                this.searchData = {};
                this.searchUserMobile = null;
            },

            openMemberTab() {
                this.addMemberSwitch = true;
            },

            searchInput() {
                this.isFixed = true;
            },

            inputBlur() {
                this.isFixed = false;
            },

            // 搜索用户
            searchUser() {
                Loading.getInstance().open();
                var _data = {
                    mobile: this.searchUserMobile
                }
                request.getInstance().getData('api/shop/user/search', _data).then(res => {
                    this.searchData = res.data.data;
                    Loading.getInstance().close();
                }).catch(err => {
                    Loading.getInstance().close();
                });
            },

            updateShop(type) {
                if (!this.isGroupMaster) {
                    return;
                }
                // 修改公会名称
                if (type == "shopName") {

                    MessageBox.prompt("请输入新的公会名称", "修改公会名称", ).then(({ value, action }) => {
                        if (value.length == 0) {
                            Toast("新公会名称不能为空");
                            return;
                        }
                        var reg = /^\s*(\S+)\s*$/;

                        if (!reg.test(value)) {
                            Toast("新公会名称格式不正确");
                            return;
                        }

                        Loading.getInstance().open();
                        var _data = {
                            name: value
                        };
                        request.getInstance().postData('api/shop/update/' + this.shopId, _data).then(res => {
                            Loading.getInstance().close();

                            Toast("公会改名成功");
                            this.init();
                        }).catch(err => {
                            Loading.getInstance().close();
                            Toast(err.data.msg);
                        });
                    }).catch(err => { });
                }

                // 手续费率
                if (type == "percent") {
                    MessageBox.prompt("请输入新的公会佣金费率", "修改公会佣金费率(必须小于"+this.fee_limit+"%)", ).then(({ value, action }) => {

                        if (value.length == 0) {
                            Toast("公会佣金费率不能为空");
                            return;
                        }

                        if (isNaN(Number(value))) {
                            Toast("请输入正确的公会佣金费率");
                            return;
                        }

                        if (Number(value) > Number(this.fee_limit)) {
                            Toast("公会佣金费率必须小于" + this.fee_limit + "%");
                            return;
                        }

                        Loading.getInstance().open();

                        var _data = {
                            percent: value
                        };

                        request.getInstance().postData('api/shop/update/' + this.shopId, _data).then(res => {
                            Loading.getInstance().close();
                            Toast("修改手续费率成功");
                            this.init();
                        }).catch(err => {
                            Loading.getInstance().close();

                            Toast(err.data.msg);
                        });
                    }).catch(err => { });
                }

                // 设置任务默认倍率
                if (type == "rate") {
                    MessageBox.prompt("请输入新的任务默认倍率(允许有1位小数)", "修改任务默认倍率", ).then(({ value, action }) => {

                        if (!value) {
                            Toast("任务默认倍率不能为空");
                            return;
                        }

                        if (isNaN(Number(value))) {
                            Toast("请输入正确的任务默认倍率");
                            return;
                        }

                        if ((parseFloat(value) * 10).toString().indexOf(".") != -1 && parseFloat(value) > 0) {
                            Toast("请输入正确的任务默认倍率(允许有1位小数)");
                            return;
                        }

                        if (parseFloat(value) >= 100000) {
                            Toast("任务默认倍率的最大值位99999");
                        }

                        var _data = {
                            rate: value
                        };

                        Loading.getInstance().open();
                        request.getInstance().postData('api/shop/update/' + this.shopId, _data).then(res => {
                            Loading.getInstance().close();

                            Toast("修任务默认倍率成功");
                            this.init();
                        }).catch(err => {
                            Loading.getInstance().close();
                            Toast(err.data.msg);
                        });
                    }).catch(err => { });
                }
            },

            SetString(str, len) {
                return utils.SetString(str, len);
            },

            changeInviteLinkStatus() {
                var _link = null;
                //      if(!this.isShow || !this.isGroupMaster){
                //        return ;
                //      }

                if (this.inviteLinkStatus == true) {
                    _link = 1;
                } else {
                    _link = 0;
                }

                var _data = {
                    use_link: _link
                };

                request.getInstance().postData('api/shop/update/' + this.shopId, _data).then(res => {

                }).catch(err => {
                    Toast("设置失败");
                    this.init();
                });
            },

            changeTradeStatus() {
                var _link = null;

                //      if(!this.isShow || !this.isGroupMaster){
                //        return ;
                //      }

                if (this.tradeStatus == true) {
                    _link = 1;
                } else {
                    _link = 0;
                }

                var _data = {
                    active: _link
                };

                request.getInstance().postData('api/shop/update/' + this.shopId, _data).then(res => {

                }).catch(err => {
                    Toast("设置失败");
                    this.init();
                });
            },

            complaint() {
                location.href = this.user_feedback;
            }


        },
        //  watch:{
        //    // 邀请链接修改
        //    "inviteLinkStatus":function(){
        //
        //
        //    },
        //
        //    "tradeStatus":function(){
        //
        //    }
        //  }
    };
</script>