<template>
    <div id="makeDealTip">
        <topBack style="background:#eee;"></topBack>
        <dealContent :renderData="renderData"></dealContent>

         <div class="tip-wrap flex flex-align-center flex-justify-around" >
            <label for="" class="flex-4" style="padding-left:1em;">大赢家茶水费</label>
            <!-- <span style="color:#999;" class="flex-4">请大家自觉缴纳</span> -->
            <span class="flex flex-align-center flex-6 flex-reverse" style="padding-right:1em;" >元<input type="text" class="tipMoney" placeholder="点击缴纳茶水费"  maxlength="6" v-model="renderData.moneyData"></span>
        </div>

        <div class="button-wrap">
            <mt-button type="primary" size="large" class="green-color-bg" @click = "payTip">交纳</mt-button>
        </div>

        <div class="tip-record">
            <h3>茶水费记录</h3>
            <ul class="flex flex-v">
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
                        <div style="font-size: 0.8em;color:#999;">已交纳</div>
                    </div>
                </li>
                <li class="flex flex-justify-between flex-align-center">
                    <img src="/images/avatar.jpg" alt="">
                    <span>玩家名字最多7格子</span>
                    <div class="flex flex-v flex-align-center">
                        <div style="font-size: 1.4em;">2</div>
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

export default {
  created() {
    this.init();
  },
  components: { topBack, dealContent, passwordTab },
  data() {
    return {
      renderData: {
        name: null,
        moneyData: null
      },
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
      Loading.getInstance().open();
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
        });
    },

    showPassWord() {
      this.passwordData.switch = true;
    },
    hidePassword(e) {
      this.passwordData.switch = false;
    },
    getPassword(e) {
      console.log(e);
      this.passwordData.value = e;
      var _id = this.$route.query.id;

      var _data = {
        transfer_id: _id,
        fee: this.renderData.moneyData,
        action: 1,
        pay_password: this.passwordData.value
      };

      request
        .getInstance()
        .postData("api/transfer/payfee", _data)
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          console.error(err);
        });
    }
  }
};
</script>