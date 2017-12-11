<template>
  <div id="shop-detail">
      <topBack style="color:#fff; background:#26a2ff;"></topBack>
      
      <div class="top flex flex-v flex-align-center">
        <div class="img-wrap flex flex-justify-center flex-align-center flex-wrap-on">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <img src="/images/avatar.jpg" alt="" class="avatar">
        </div>
        <h3 style="margin-top:0.5em;">店铺1111</h3>
        <h3>店铺id:123123123</h3>
      </div>

      <div class="menu flex " v-if="isGroupMaster">
          <div class="menu-item flex flex-v flex-align-center flex-justify-around">
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
          <div class="menu-item flex flex-v flex-align-center flex-justify-around">
              <i class="iconfont">
                  &#xe603;
              </i>
              <h3>
                  店铺订单
              </h3>
          </div>
      </div>

    <div class="shop-info">

        <div class="info-item flex flex-align-center flex-justify-between">
            <span class="title flex-4"> 店铺名称 </span>
            <span class="name flex-5">热血牛牛玩家群1</span>
            <i class="iconfont flex-1">
            &#xe62e;
            </i>
        </div>

        <div class="shop-qrcode flex flex-align-center flex-justify-between">
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
            <div class="avatar-item">
                <img src="/images/avatar.jpg" alt="">
            </div>
            <div class="avatar-item">
                <img src="/images/avatar.jpg" alt="">
            </div>
            <div class="avatar-item">
                <img src="/images/avatar.jpg" alt="">
            </div>
            

            <div class="add-avatar flex flex-align-center flex-justify-center">
                <i class="iconfont">
                    &#xe600;
                </i>
            </div>
        </div>

        <div class="flex-4 flex flex-reverse">
            
            <i class="iconfont" style="padding-right:1em;">
                &#xe62e;
            </i>
            <span style="color:#666;">0名成员</span>
        </div>

    </div>

    <div class="invite-wrap">
        <div class="flex flex-align-center flex-justify-between">
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

        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9"> 默认单价 </span>
            <span class="text flex-1">10</span>
        </div>
        
    </div>

    <div class="commission" v-if="isGroupMaster">
        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9"> 抽水比例 </span>
            <span class="text flex-1">5%</span>
        </div>

        <div class="flex flex-align-center flex-justify-between">
            <span class="title flex-9"> 是否开启交易功能 </span>
            <span class="text flex-1 flex flex-reverse">
                <mt-switch v-model="tradeStatus" ></mt-switch>
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
        <mt-button type="danger" size="large">解散店铺</mt-button>
    </div>

  </div>
</template>

<style lang="scss" scoped>
#shop-detail {
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
        width: 30%;
        height: 30%;
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
        color:#555;
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
          margin-left:0.2em;
        }
      }

      .add-avatar {
        box-sizing: border-box;
        width: 2.3em;
        border-radius: 0.4em;
        height: 2.3em;
        border:0.1em solid #ccc;
        margin-left: 0.2em;

        >i{
            font-size: 2em;
            color:#ccc;
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
      &:nth-child(1) {
        border-bottom: 0.05em solid #eee;
      }
      box-sizing: border-box;
      height: 2.5em;
      padding-left: 1em;
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

  .complaint{
    @extend .commission;
  }

  .button-wrap {
    width: 90%;
    margin: 0 auto;
    margin-top: 1em;
    padding-bottom: 1em;
  }
}
</style>

<script>
import topBack from "../../components/topBack";

export default {
  name: "shopDetail",
  components: { topBack },
  data() {
    return {
      inviteLinkStatus: true,    // 邀请链接状态
      tradeStatus: true,         // 交易状态
      isGroupMaster:true        // 是否是群主 
    };
  },
  methods: {
    hide(){
    },
    goMember(){
      this.$router.push("/shop/shop_member");
    },
    goDealManagement(){
      this.$router.push("/shop/deal_management");
    }
  }
};
</script>

