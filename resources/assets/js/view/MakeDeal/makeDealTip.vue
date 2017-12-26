<template>
    <div id="makeDealTip">
        <topBack style="background:#eee;"></topBack>
        <dealContent :renderData="renderData"></dealContent>

         <div class="tip-wrap flex flex-align-center flex-justify-around" >
            <label for="" class="flex-4" style="padding-left:1em;">打赏店家</label>
            <!-- <span style="color:#999;" class="flex-4">请大家自觉缴纳</span> -->
            <span class="flex flex-align-center flex-6 flex-reverse" style="padding-right:1em;" >元<input type="text" class="tipMoney" placeholder="点击打赏店家"  maxlength="6" v-model="renderData.moneyData"></span>
        </div>

        <div class="button-wrap">
            <mt-button type="primary" size="large" class="green-color-bg" @click = "payTip">交纳</mt-button>
        </div>

        <div class="tip-record">
            <h3>打赏店家记录</h3>
            <ul class="flex flex-v">
                <li class="flex flex-justify-between flex-align-center" v-for="item in renderData.tips">
                    <img :src="item.user.avatar?item.user.avatar:'/images/default_avatar.jpg'" alt="">
                    <span>{{item.user.name}}</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">{{item.amount}}</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
            </ul>

        </div>

        <passwordTab :setSwitch = "passwordData.switch" v-on:hidePassword="hidePassword" v-on:callBack="getPassword"></passwordTab>
    </div>
</template>

<style lang="scss" scoped>
#makeDealTip {
  min-height: 100vh;
  background: #eee;
  padding-top: 2em;
  box-sizing: border-box;
}

.tip-wrap {
  width: 90%;
  height: 2.5em;
  background: #fff;
  border-radius: 0.2em;
  margin: 0 auto;

  .tipMoney {
    outline: none;
    font-size: 1em;
    width: 70%;
    height: 100%;
    display: block;
    border: none;
  }
}

.button-wrap {
  width: 90%;
  margin: 0 auto;
  margin-top: 0.5em;
}

.green-color-bg {
  background: #11bb00;
}

.tip-record {
  margin-top: 3em;
  h3 {
    width: 90%;
    margin: 0 auto;
    font-size: 0.9em;
    color: #666;
  }

  ul {
    width: 90%;
    margin: 0 auto;
    margin-top: 0.5em;

    li {
      border-bottom: 1px solid #ccc;
      height: 3.2em;
      > img {
        width: 2.5em;
        height: 2.5em;
      }

      > span {
        color: #555;
      }
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import dealContent from "./dealContent";
import Loading from "../../utils/loading";
import request from "../../utils/userRequest";
import passwordTab from "../../components/password";
import { Toast } from "mint-ui";

export default {
  created() {
    this.init();
  },
  components: { topBack, dealContent, passwordTab },
  data() {
    return {
      renderData: {
        name: null,
        moneyData: null,
      },
      // hasPayList:[],
      passwordData: {
        switch: false,
        value: null
      }
    };
  },
  methods: {
    init() {
      Loading.getInstance().open();
      var _id = this.$route.query.id;
      request
        .getInstance()
        .getData("api/transfer/feerecord" + "?transfer_id=" + _id)
        .then(res => {
          console.log(res);
          this.renderData = res.data.data;
          Loading.getInstance().close();
        })
        .catch(err => {
          console.error(err);
        });
    },
    payTip() {
      if (this.renderData.moneyData == null) {
        Toast("请输入打赏店家金额");
        return;
      }

      Loading.getInstance().open();

      request
        .getInstance()
        .getData("api/my/info")
        .then(res => {
          console.log(res);

          // 判断是否已经设置了支付密码
          if (!res.data.data.has_pay_password) {
            Toast("您还未设置支付密码，即将跳转设置页面");
            setTimeout(() => {
            Loading.getInstance().close();
              
              this.$router.push("/my/setting_password");
            }, 2000);
            return Promise.resolve(false);
          } else {
            return Promise.resolve(true);
          }
        })
        .then(res => {
          // 验证支付的数据
          if (res == true) {
            var _id = this.$route.query.id;
            var _data = {
              transfer_id: _id,
              fee: this.renderData.moneyData,
              action: 0
            };

            request
              .getInstance()
              .postData("api/transfer/payfee", _data)
              .then(res => {
                console.log(res);
                this.passwordData.switch = true;
                Loading.getInstance().close();
              })
              .catch(err => {
                console.error(err);
                Loading.getInstance().close();
                Toast(err.data.msg);       
              });
          }
          console.log(res);
        })
        .catch(err => {
          console.error(err);
        });
    },

    showPassWord() {
      this.passwordData.switch = true;
    },
    hidePassword() {
      this.passwordData.switch = false;
    },
    getPassword(e) {
      this.passwordData.value = e;
      var _id = this.$route.query.id;

      var _data = {
        transfer_id: _id,
        fee: this.renderData.moneyData,
        action: 1,
        pay_password: this.passwordData.value
      };

      // 打赏店家接口
      request
        .getInstance()
        .postData("api/transfer/payfee", _data)
        .then(res => {
          console.log(res);
          Toast("打赏店家成功");
          this.hidePassword();
          this.init();
        })
        .catch(err => {
          console.error(err);
          Loading.getInstance().close();
          Toast(err.data.msg);
        });
    }
  }
};
</script>