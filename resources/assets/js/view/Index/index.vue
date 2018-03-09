<template>
  <div id="index">
    <section id="top">
      <div class="message flex flex-reverse flex-align-center" @click="goInform">
        <i class="iconfont" style="color:#fff; font-size:1.5em;">
          &#xe626;
        </i>

        <span class="notice" v-if="newMessage>0">
            </span>
      </div>

      <section class="transaction flex flex-justify-center">
        <div class="left flex-3">
          <!-- <a href="/#/myAccount"> -->
          <div class="imggWrap flex flex-justify-center flex-align-center">
            <img :src="avatar" alt="">
          </div>
          <h2>{{userName}}</h2>
          <!-- </a> -->
        </div>

        <div class="center flex-5 flex flex-v flex-align-center flex-justify-center" @click="goMyAccount">
          <h3 class="flex">{{amount}} <i class="diamond" style="margin-top:0.1em;margin-left:0.4em;">&#xe6f9;</i></h3>
          <h4>账户余额(钻石)</h4>
        </div>

        <div class="right flex-2">
              <span class="flex flex-align-center" @click="goMyAccount">
                <!--<i class="iconfont">&#xe6bd;</i>-->
                <span class="wallet-icon">
                  <img src="/images/qianbao.png" alt="">
                </span>
                <i class="iconfont">&#xe62e;</i>              
              </span>
        </div>

      </section>

    </section>

    <section class="content">

      <div class="menu-class flex flex-align-center">
        <span></span>我的
      </div>

      <ul class="flex flex-wrap-on">

        <li class="flex flex-v flex-align-center">
          <a href="/#/shop" class="flex flex-v flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe611;
            </i>
            <h3>我的公会</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a href="/#/makeDeal/my_deal" class="flex flex-v flex-align-center">
            <i class="iconfont transaction-icon common-icon">
              &#xe605;
            </i>
            <h3>我的任务</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goShareProfit">
            <i class="iconfont transaction-icon common-icon">
              &#xe61d;
            </i>
            <h3>我的分润</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goMyUsers">
            <i class="iconfont transaction-icon common-icon">
              &#xe621;
            </i>
            <h3>我的用户</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center">
          <a href="/#/my_vip" class="flex flex-v flex-align-center">
            <i class="iconfont transaction-icon common-icon">
              &#xe6e1;
            </i>
            <h3>我的vip</h3>
          </a>
        </li>

      </ul>

      <div class="menu-class flex flex-align-center">
        <span></span>拓展
      </div>

      <ul class="flex flex-wrap-on">

        <li class="flex flex-v flex-align-center">
          <a href="/#/shareUser" class="flex flex-v flex-align-center">
            <i class="iconfont transaction-icon common-icon">
              &#xe64f;
            </i>
            <h3>展业</h3>
          </a>
        </li>

        <li class="flex flex-v flex-align-center" v-if="isPromoters==1">
          <a class="flex flex-v flex-align-center" @click="goVipOpenCard">
            <i class="iconfont transaction-icon common-icon">
              &#xe650;
            </i>
            <h3>vip开卡</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goDealList">
            <i class="iconfont transaction-icon common-icon">
              &#xe617;
            </i>
            <h3>前往交易行</h3>
          </a>
        </li>
        <li class="flex flex-v flex-align-center">
          <a class="flex flex-v flex-align-center" @click="goSafety">
            <i class="iconfont transaction-icon common-icon">
              &#xe69a;
            </i>
            <h3>安全保障</h3>
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

    .message {
      height: 2em;
      width: 100%;
      padding-right: 1em;
      box-sizing: border-box;
      position: relative;

      .notice {
        width: 0.6em;
        height: 0.6em;
        background: red;
        border-radius: 50%;
        position: absolute;
        right: 0.7em;
        top: 0em;
      }
    }

    .transaction {
      width: 100%;
      height: 8em;

      .imggWrap {
        height: 5em;

        > img {
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

      h3 {
        font-size: 1.8em;
        text-align: center;
        color: #fff;
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

        > span {
          margin-top: 0.5em;

          > i {
            font-size: 2.5em;
            color: #fff;
          }

          i:nth-child(1) {
            color: #1b53af;
          }

          i:nth-child(2) {
            font-size: 1.0em;
          }

          .wallet-icon {
            width: 2.0em;
            > img {
              width: 100%;
            }
          }
        }
      }
    }
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

      > span {
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
        height: 6em;

        .common-icon {
          font-size: 2em;
          color: #26a2ff;
        }
        h3 {
          color: #000;
          margin-top: 0.4em;
          font-size: 0.90em;
        }
      }

    }
  }
</style>

<script>
  import tabBar from "../../components/tabBar";
  import Loading from "../../utils/loading"
  import request from "../../utils/userRequest"
  import {MessageBox, Toast} from 'mint-ui'

  export default {
    name: "index",
    components: {tabBar},
    data() {
      return {
        amount: null,
        avatar: null,
        newMessage: 0,
        userName: "",

        isAgent: 0,    // 是否是代理
        isPromoters: 0  // 是否是推广员

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

      init() {
        Loading.getInstance().open();
        request.getInstance().getData("api/index").then(res => {
          this.amount = res.data.data.balance;
          this.avatar = res.data.data.avatar;
          this.newMessage = res.data.data.new_message;
          this.userName = res.data.data.name;

          this.isAgent = res.data.data.is_agent;
          this.isPromoters = res.data.data.is_promoter;
          Loading.getInstance().close();

        }).catch(err => {
          Loading.getInstance().close();
        });

      }
    }
  };
</script>
