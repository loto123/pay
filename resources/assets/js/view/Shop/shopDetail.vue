<template>
  <div id="shop-detail" v-if="isShow">
      <topBack style="color:#fff; background:#26a2ff;"></topBack>
      
      <div class="top flex flex-v flex-align-center">
        <div class="img-wrap flex flex-justify-center flex-align-center flex-wrap-on">
            <img :src="logo" alt="" class="avatar">
        </div>
        <h3 style="margin-top:0.5em;">{{shopName}}</h3>
        <h3>店铺id:{{shopId}}</h3>
      </div>

      <div class="menu flex " v-if="isGroupMaster">
          <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goShopAccount">
              <i class="iconfont">
                  &#xe61e;
              </i>
              <h3>店铺账户</h3>
          </div>

          <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goDealManagement">
              <i class="iconfont">
                  &#xe63b;
              </i>
              <h3>交易管理</h3>
          </div>
          <div class="menu-item flex flex-v flex-align-center flex-justify-around" @click="goShopOrder">
              <i class="iconfont">
                  &#xe603;
              </i>
              <h3>
                  店铺订单
              </h3>
          </div>
      </div>

    <div class="shop-info">

        <div class="info-item flex flex-align-center flex-justify-between" @click="updateShop('shopName')">
            <span class="title flex-4"> 店铺名称 </span>
            <span class="name flex-5">{{shopName}}</span>
            <i class="iconfont flex-1">
            &#xe62e;
            </i>
        </div>

        <div class="shop-qrcode flex flex-align-center flex-justify-between" @click="invite">
            <span class="title flex-8">店铺二维码</span>
            <span class="qr-code flex-1">
                <i class="iconfont">
                    &#xe9c4;
                </i>
            </span>
            <i class="iconfont flex-1">
            &#xe62e;
            </i>
        </div>
    </div>

    <div class="member-wrap flex flex-align-center flex-justify-around" @click="goMember">

        <div class="flex-1" style="padding-left:1em;">
            <i class="iconfont icon">
                &#xe73c;
            </i>
        </div>

        <div class="avatar-wrap flex-5 flex flex-justify-around">
            <div class="avatar-item" v-for="item in membersList">
                <img :src="item.avatar" alt="">
            </div>
            
            <div class="add-avatar flex flex-align-center flex-justify-center" @click.stop="addMember">
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
                <mt-switch v-model="inviteLinkStatus"></mt-switch>
            </span>
        </div>
    </div>

    <div class="platform">
        <div class="flex flex-align-center flex-justify-between" v-if="isGroupMaster">
            <span class="title flex-9"> 平台交易费 </span>
            <span class="text flex-1">5%</span>
        </div>

        <div class="flex flex-align-center flex-justify-between" @click="updateShop('rate')">
            <span class="title flex-9"> 默认单价 </span>
            <span class="text flex-1">{{rate}}</span>
        </div>
        
    </div>

    <div class="commission" v-if="isGroupMaster">
        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9" @click="updateShop('percent')"> 手续费率 </span>
            <span class="text flex-1">{{percent}}%</span>
        </div>

        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9"> 是否开启交易功能 </span>
            <span class="text flex-1 flex flex-reverse">
                <mt-switch v-model="tradeStatus"></mt-switch>
            </span>
        </div>
    </div>

    <div class="complaint" v-if="!isGroupMaster">
        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9"> 投诉 </span>
            <span class="text flex-1"></span>
        </div>
    </div>

    <div class="button-wrap">
        <mt-button type="danger" size="large" @click = "dissShop">解散店铺</mt-button>
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

      <div class="submit flex flex-justify-center" v-if="searchData.id" @click="submitAddMember">
        <mt-button type="default" size="large" style="width:70%;">邀请</mt-button>
      </div>

      </div>
    </div>

  </div>
</template>

<style lang="scss" scoped>
#shop-detail {
  .poAbsolute{
    position: absolute !important;
  }

  background: #eee;
  min-height: 100vh;

  .top {
    padding-top: 2em;
    height: 10em;
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

      > i {
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
    height: 5em;
    width: 100%;

    .info-item {
      height: 2.5em;
      width: 100%;
      border-bottom: 0.05em solid #eee;

      .title {
        box-sizing: border-box;
        padding-left: 1em;
      }

      > i {
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

      > i {
        box-sizing: border-box;
        padding-right: 1em;
        text-align: right;
      }

      .qr-code {
        text-align: right;
        > i {
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
        > img {
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

        > i {
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
    width: 100%;
    // height: 5em;
    background: #fff;
    margin-top: 0.5em;

    > div {
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
    width: 100%;
    // height: 5em;
    background: #fff;
    margin-top: 0.5em;

    > div {
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

    > div {
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

  .add-members-pop{
    width:100%;
    height: 100vh;
    /*position: absolute;*/
    position: fixed;
    background: rgba(0,0,0,0.7);
    top:0em;
    left: 0em;

    .content-tab{
      width:90%;
      height:16em;
      background:#fff;
      border-radius:1em;

      .top-content{
        width:100%;
        height: 3em;

        >h3{
          text-align:center;
        }
      }

      .middle-content{
        width:100%;
        height: 3em;
        box-sizing: border-box;
        border:1px solid #bbb;

        .input-wrap{
          width:100%;
          height:3em;
          box-sizing: border-box;

          >input{
            display: block;
            outline: none;
            border:none;
            height: 75%;
            width: 85%;
            text-indent: 2em;
            font-size:1.1em;
          }
        }

        .search-btn{
          width:100%;
          height: 100%;
          border:1px solid #bbb;
        }
      }

      .user-info{
        height: 6em;
        width:100%;

        .info{
          width:90%;
          height: 6em;

          .info-wrap{
            >img{
              width:4em;
              height:4em;
              border-radius:0.4em;
            }
          }

          .info-right{
            >span{
              margin-top:1em;
              display: block;
              width:100%;
              text-align:left;
            }
          }



        }
      }

      .submit{
        width:100%;
      }
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import { Toast,MessageBox } from "mint-ui";
import request from "../../utils/userRequest";
import Loading from "../../utils/loading";

export default {
  name: "shopDetail",
  beforeMount() {},
  created() {
    this.init();
  },
  mounted() {},
  components: { topBack },
  data() {
    return {
      isShow:false,

      inviteLinkStatus: true,    // 邀请链接状态
      tradeStatus: true,         // 交易状态
      isGroupMaster: true,       // 是否是群主
      searchUserMobile:null,     // 搜索店铺成员的手机号

      isFixed:false,

      shopId: null,
      shopName: null,
      rate: null,
      percent: null,
      membersCount: null,
      membersList:[],
      active: null,

      addMemberSwitch: false,      // 添加成员开关
      logo:null,                    // 店铺的头像

      searchData:{                 // 搜索出来的数据
        avatar:null,
        id:null,
        mobile:null,
        name:null
      }
    };
  },
  methods: {
    // 跳转控制
    hide() {},
    goMember() {
      if(!this.membersCount){
        Toast("当前店铺无成员,");
        return ;
      }
      this.$router.push("/shop/shop_member?shopId="+this.shopId);
    },
    goDealManagement() {
      this.$router.push("/shop/deal_management?shopId="+this.shopId);
    },
    goShopAccount() {
      this.$router.push("/shop/shopAccount?id="+this.shopId);
    },
    goShopOrder() {
      this.$router.push("/shop/shopOrder");
    },

    invite(){
      this.$router.push("/shop/shopShare?id="+this.shopId);
    },

    addMember(){
      this.openMemberTab();
    },

    // 发送邀请用户请求
    submitAddMember(){
      Loading.getInstance().open();
      var _data= {
        shop_id:this.shopId,
        user_id:this.searchUserMobile
      }

      request.getInstance().postData("api/shop/invite/"+this.shopId+"/"+this.searchData.id).then(res=>{
        Loading.getInstance().close();
        Toast("邀请用户成功");
        this.closeMemberTab();
      }).catch(error=>{
      });
    },

    // 数据控制
    init() {
      Loading.getInstance().open();
      var self = this;
      var _id = this.$route.query.id;

      request
        .getInstance()
        .getData("api/shop/detail/" + _id)
        .then(res => {
          this.isShow = true;
          this.isGroupMaster = res.data.data.is_manager;
          this.shopId = res.data.data.id;
          this.shopName = res.data.data.name;
          this.rate = res.data.data.rate;
          if(this.isGroupMaster){
              this.percent = res.data.data.percent;
          }
          this.membersCount = res.data.data.members_count;
          this.membersList = res.data.data.members;
          this.logo = res.data.data.logo;


          if (res.data.data.active == 1) {
            this.tradeStatus = true;
          } else {
            this.tradeStatus = false;
          }

          Loading.getInstance().close();
        })
        .catch(error => {
          Toast("当前页面不存在");
          this.$router.go(-1);
        });
    },

    // 解散店铺
    dissShop() {
        MessageBox.confirm('确定删除店铺?').then(action => {

            Loading.getInstance().open();

            request
              .getInstance()
              .postData("api/shop/close/" + this.shopId)
              .then(res => {
                Loading.getInstance().close();
                Toast("店铺解散成功");
                setTimeout(()=>{
                  this.$router.push("/shop");
                },1000);
              })
              .catch(error => {
                console.error(error);
              });
        }).catch(err=>{

        });
      
    },

    closeMemberTab(){
      this.addMemberSwitch = false;
      this.searchData = {};
      this.searchUserMobile = null;
    },

    openMemberTab(){
      this.addMemberSwitch = true;
    },

    searchInput(){
      this.isFixed = true;
    },

    inputBlur(){
      this.isFixed = false;
    },

    // 搜索用户
    searchUser(){
      Loading.getInstance().open();
      var _data = {
        mobile :this.searchUserMobile
      }
      request.getInstance().getData('api/shop/user/search',_data).then(res=>{
        this.searchData = res.data.data;
        Loading.getInstance().close();
      }).catch(err=>{
        Loading.getInstance().close();
      });
    },

    updateShop(type){
      if (!this.isGroupMaster){
          return;
      }
      // 修改店铺名称
      if(type == "shopName"){

        MessageBox.prompt("请输入新的店铺名称","修改店铺名称",).then(({ value, action }) => {
          if(value.length ==0){
            Toast("新店铺名称不能为空");
            return;
          }
          Loading.getInstance().open();
          var _data = {
            name:value
          };
          request.getInstance().postData('api/shop/update/'+this.shopId,_data).then(res=>{
            Loading.getInstance().close();
            
            Toast("店铺改名成功");
            setTimeout(()=>{
              this.init();
            },1500);
          }).catch(err=>{
            Loading.getInstance().close();
            Toast(err.data.data.msg);
          });  
        }).catch(err=>{});
      }

      // 手续费率
      if(type=="percent"){
         MessageBox.prompt("请输入新的手续费率","修改手续费率",).then(({ value, action }) => {
          
          if(value.length ==0){
            Toast("手续费率不能为空");
            return;
          }
          Loading.getInstance().open();
          
          var _data = {
            percent:value
          };

          request.getInstance().postData('api/shop/update/'+this.shopId,_data).then(res=>{
            Loading.getInstance().close();
            Toast("修改手续费率成功");
            setTimeout(()=>{
              this.init();
            },1500);
          }).catch(err=>{
            Loading.getInstance().close();
            
            Toast(err.data.data.msg);
          });  
        }).catch(err=>{});
      }

      // 设置单价
      if(type=="rate"){
          MessageBox.prompt("请输入新的单价","修改单价",).then(({ value, action }) => {
            if(!value){
              Toast("单价不能为空");
              return;
            }

            var _data = {
              rate:value
            };
            Loading.getInstance().open();
            request.getInstance().postData('api/shop/update/'+this.shopId,_data).then(res=>{
              Loading.getInstance().close();
              
              Toast("修改单价成功");
              setTimeout(()=>{
                this.init();
              },1500);
            }).catch(err=>{
              Loading.getInstance().close();
              Toast(err.data.data.msg);
            });  
          }).catch(err=>{});
        }
    },

  },
  watch:{
    // 邀请链接修改
    "inviteLinkStatus":function(){

      var _link = null;

      if(!this.isShow || !this.isGroupMaster){
          return ;
      }

      if(this.inviteLinkStatus == true){
        _link = 1;
      }else {
        _link = 0;
      }

      var _data = {
        use_link:_link
      };

      request.getInstance().postData('api/shop/update/'+this.shopId,_data).then(res=>{

      }).catch(err=>{
          Toast("设置失败");
          this.init();
      });
    },

    "tradeStatus":function(){
      var _link = null;
      if(!this.isShow || !this.isGroupMaster){
          return ;
      }

      if(this.tradeStatus == true){
        _link = 1;
      }else {
        _link = 0;
      }

      var _data = {
        active:_link
      };

      request.getInstance().postData('api/shop/update/'+this.shopId,_data).then(res=>{

      }).catch(err=>{
          Toast("设置失败");
          this.init();
      });
    }
  }
};
</script>

