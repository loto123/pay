<template>
  <div id="index">
    <section id="top">
      <div class="message flex flex-reverse flex-align-center" @click="goInform">
        <div class="news">
          <img src="/images/news.png">
        </div>
        <span class="notice" v-if="newMessage>0">
        </span>
      </div>

      <section class="transaction flex flex-justify-start">
        <div class="left flex-3">
          <!-- <a href="/#/myAccount"> -->
          <div class="imggWrap flex flex-justify-center flex-align-start">
            <img :src="avatar" alt="">
          </div>
          <h2>{{userName}}</h2>
          <!-- </a> -->
        </div>

        <div class="center flex-5 flex flex-v flex-align-start flex-justify-center" @click="goMyAccount">
          <div class="flex diamond-box">{{amount}}
            <div class="diamond">
              <img src="/images/zuanshi.png">
            </div>
          </div>
          <h4>账户余额(钻石)</h4>
        </div>
      </section>
      <div class="wallet-container">
        <div class="flex flex-align-center wallet-content" @click="goMyAccount">
          <div class="wallet-box">
            <img src="/images/qianbao.png">
          </div>
          <div class="wallet-icon">
            <img src="/images/icon_qianbao.png">
          </div>
          <div class="my-wallet">我的钱包</div>
          <div class="right-icon">
            <i class="iconfont">&#xe62e;</i>
          </div>
        </div>
      </div>
    </section>

    <section class="content">

      <div class="menu-class flex flex-align-center">
        <span></span>我的
      </div>

      <ul class="flex flex-wrap-on">
        <li class="flex flex-v flex-align-center" v-for="(item,index) in myList">
          <a v-bind:href="item.url" class="flex flex-v flex-align-center shop-notice-box">
            <div class="home-icon">
              <img :src="item.logo" alt="">
            </div>
            <h3>{{item.name}}</h3>
            <span class="shop-notice" v-if="messageCount">
              {{this.messageCount>99?"99":this.messageCount}}
            </span>
          </a>
        </li>

        <!-- <li class="flex flex-v flex-align-center">
          <a href="/#/makeDeal/my_deal" class="flex flex-v flex-align-center">
            <div class="home-icon">
              <img src="/images/home/renwu.png" alt="">
            </div>
            <h3>我的任务</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goShareProfit">
            <div class="home-icon">
              <img src="/images/home/fenrun.png" alt="">
            </div>
            <h3>我的分润</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goMyUsers">
            <div class="home-icon">
              <img src="/images/home/yonghu.png" alt="">
            </div>
            <h3>我的用户</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a href="/#/my_vip" class="flex flex-v flex-align-center">
            <div class="home-icon">
              <img src="/images/home/vip.png" alt="">
            </div>
            <h3>我的vip</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a href="/#/MyReward" class="flex flex-v flex-align-center">
            <div class="home-icon">
              <img src="/images/home/shangjin.png" alt="">
            </div>
            <h3>我的赏金</h3>
          </a>
        </li> -->
      </ul>

      <div class="menu-class flex flex-align-center">
        <span></span>拓展
      </div>

      <ul class="flex flex-wrap-on">

        <li class="flex flex-v flex-align-center">
          <a href="/#/shareUser" class="flex flex-v flex-align-center">
            <div class="home-icon">
              <img src="/images/home/zhanye.png" alt="">
            </div>
            <h3>展业</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center" v-if="isPromoters==1">
          <a class="flex flex-v flex-align-center" @click="goVipOpenCard">
            <div class="home-icon">
              <img src="/images/home/kaika.png" alt="">
            </div>
            <h3>vip开卡</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goDealList">
            <div class="home-icon">
              <img src="/images/home/jiaoyihang.png" alt="">
            </div>
            <h3>前往交易行</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goSafety">
            <div class="home-icon">
              <img src="/images/home/baozhang.png" alt="">
            </div>
            <h3>安全保障</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goAuthAgent">
            <div class="home-icon">
              <img src="/images/home/baozhang.png" alt="">
            </div>
            <h3>授权代理</h3>
          </a>
        </li>
      </ul>

    </section>
    <tabBar :status="'index'"></tabBar>
  </div>

</template>

<style lang="scss" scoped>
  #index {
    background: #f5f7fb;
    height: 100vh;
  }

  i {
    display: block;
  }

  #top {
    height: 12em;
    background: #26a2ff;
    box-sizing: border-box;
    position: relative;
    .message {
      height: 2em;
      width: 100%;
      padding-right: 1em;
      box-sizing: border-box;
      .news {
        position: relative;
        width: 32px;
        margin-top: 1em;
        img {
          display: block;
          width: 100%;
        }
      }
      .notice {
        width: 0.6em;
        height: 0.6em;
        background: red;
        border-radius: 50%;
        position: absolute;
        right: 0.7em;
        top: 0.3em;
      }
    }

    .transaction {
      width: 100%;
      height: 6em;

      .imggWrap {
        height: 5em;

        >img {
          width: 4em;
          height: 4em;
          border-radius: 50%;
          display: block;
        }
      }

      h2 {
        text-align: center;
        color: #fff;
      }
      .diamond-box {
        font-size: 1.8em;
        text-align: center;
        color: #fff;
        .diamond {
          width: 30px;
          margin-left: 0.2em;
          img {
            display: block;
            width: 100%;
          }
        }
      }

      h4 {
        color: #fff;
        font-size: 0.95em;
        margin-top: 0.6em;
      }

      .left {
        padding-top: 1em;
        box-sizing: border-box;
      }

      .right {
        padding-top: 6em;
        box-sizing: border-box;

        >span {
          margin-top: 0.5em;

          >i {
            font-size: 2.5em;
            color: #fff;
          }

          i:nth-child(1) {
            color: #1b53af;
          }

          i:nth-child(2) {
            font-size: 1.0em;
          }

        }
      }
    }
  }

  .wallet-container {
    width: 35%;
    position: absolute;
    right: 0;
    bottom: 1em;
  }

  .wallet-box {
    width: 100%;
    position: relative;
    img {
      display: block;
      width: 100%;
    }
  }
  .wallet-icon {
    position: absolute;
    left: 5%;
    width: 20%;
    img {
      display: block;
      width: 100%;
    }
  }
  .my-wallet{
    position: absolute;
    left: 30%;
    color: #fff;
  }
  .right-icon{
    position: absolute;
    right:0.2em;
    color: #fff;
  }

  .content {
    box-sizing: border-box;
    padding-bottom: 3.2em;

    .menu-class {
      background: #fff;
      margin-top: 0.5em;
      height: 2em;
      padding-left: 0.5em;
      box-sizing: border-box;

      >span {
        width: 0.4em;
        height: 85%;
        background: #26a2ff;
        margin-right: 0.5em;
      }
    }

    ul {
      background: #fff;

      li {
        padding-top: 0.5em;
        box-sizing: border-box;
        width: 33.33%;
        height: 5.5em;
        .home-icon {
          width: 35px;
          img {
            display: block;
            width: 100%;
          }
        }
        h3 {
          color: #000;
          margin-top: 0.4em;
          font-size: 0.9em;
        }
      }

    }
  }

  .shop-notice-box {
    position: relative;
  }

  .shop-notice {
    position: absolute;
    background: red;
    width: 1.5em;
    height: 1.5em;
    right: 0.5em;
    border-radius: 50%;
    text-align: center;
    line-height: 1.5em;
    font-size: 0.1em;
    color: #fff;
  }
</style>

<script>
  import tabBar from "../../components/tabBar";
  import Loading from "../../utils/loading"
  import request from "../../utils/userRequest"
  import { MessageBox, Toast } from 'mint-ui'

  export default {
    name: "index",
    components: { tabBar },
    data() {
      return {
        amount: null,
        avatar: null,
        newMessage: 0,
        userName: "",

        isAgent: 0,    // 是否是代理
        isPromoters: 0,  // 是否是推广员
        messageCount: null,             // 新消息数量

        myList:null     //我的列表
      }
    },
    created() {
      this.init();
    },

    methods: {
      goMyAccount() {
        this.$router.push('/myAccount');
      },

      goInform() {
        this.$router.push("/inform");
      },
      goDealList() {
        this.$router.push('/salePet')
      },
      goShareProfit() {
        if (this.isAgent == 0) {

          MessageBox({
            title: '温馨提示',
            message: '此功能只对代理开放，是否开通代理？?',
            confirmButtonText: '开通',
            showCancelButton: true
          }).then(res => {

            if (res != "confirm") {
              return;
            }

            request.getInstance().postData("api/proxy/create").then(res => {
              Toast("成功开通代理...");
              this.init();
            }).catch(err => {
              Toast(err.data.msg)
            })
          });

        } else if (this.isAgent == 1) {
          this.$router.push("/share_profit");
        }
      },
      goVipOpenCard() {
        this.$router.push("/vipCard");
      },
      goMyUsers() {
        if (this.isAgent == 0) {
          MessageBox({
            title: '温馨提示',
            message: '此功能只对代理开放，是否开通代理？?',
            confirmButtonText: '开通',
            showCancelButton: true
          }).then(res => {
            if (res != "confirm") {
              return;
            }

            request.getInstance().postData("api/proxy/create").then(res => {
              Toast("成功开通代理...");
              this.init();
            }).catch(err => {
              Toast(err.data.msg)
            })
          });

        } else if (this.isAgent == 1) {
          this.$router.push("/my/my_users");
        }
      },
      goSafety() {
        this.$router.push('/safety')
      },
      goAuthAgent(){
        this.$router.push('/authAgent')
      },
      init() {
        Loading.getInstance().open();
        Promise.all([request.getInstance().getData("api/index"), request.getInstance().getData("api/shop/messages/count"), request.getInstance().getData("api/index/module")])
          .then(res => {
            this.amount = res[0].data.data.balance;
            this.avatar = res[0].data.data.avatar;
            this.newMessage = res[0].data.data.new_message;
            this.userName = res[0].data.data.name;

            this.isAgent = res[0].data.data.is_agent;
            this.isPromoters = res[0].data.data.is_promoter;

            this.messageCount = res[1].data.data.count;

            this.myList=res[2].data.data[0].list;
            console.log(this.myList);
            console.log(res[2].data.data[0]);
            Loading.getInstance().close();

          }).catch(err => {
            Loading.getInstance().close();
          });

      }
    }
  };
</script>