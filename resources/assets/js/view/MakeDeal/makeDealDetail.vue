<template>
    <div id="deal-detail">
        <topBack style="background:#eee;">
          <div style="width:100%;padding-right:1em;box-sizing:border-box;" class="flex flex-reverse">撤销交易</div>
        </topBack>

        <section class="big-winner-tip flex flex-v flex-align-center flex-justify-center" @click="goTipPage">
            <p>大赢家</p>
            <p>茶水费</p>
        </section>
        
        <deal-content :renderData = "renderData"></deal-content>

        <section class="pay-wrap flex flex-v flex-align-center">

            <div class="pay-money flex flex-align-center flex-justify-around">
                <label for="">我要付钱</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数" v-model="moneyData.payMoney">
                </div>
            </div>

            <div class="get-money flex flex-align-center flex-justify-around">
                <label for="">我要拿钱</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数" v-model="moneyData.getMoney">
                </div>
            </div>

            <mt-button type="primary" size="large" @click="callPassword">确认</mt-button>
        </section>
        
        <!-- 参与玩家记录 -->
        <section class="pay-record ">
            <div class="title flex flex-v">
              <div class="top flex flex-align-center">
                <span>参与人</span>
              </div>

              <div class="bottom flex flex-align-center flex-justify-between">
                <img src="/images/avatar.jpg" alt="">
                <img src="/images/avatar.jpg" alt="">
                <img src="/images/avatar.jpg" alt="">
                <img src="/images/avatar.jpg" alt="">
                <img src="/images/avatar.jpg" alt="">
                
                <span class="info-friend">提醒好友</span>
              </div>
            </div>
            
            <ul class="flex flex-v flex-align-center">
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="true">
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v flex-justify-between flex-align-center">
                                <span class="money">-100</span>
                                <span class="title">付钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
                
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" >
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v  ">
                                <span class="money green-color">+100</span>
                                <span class="title">拿钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="true">
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v flex-justify-between flex-align-center">
                                <span class="money">-100</span>
                                <span class="title">付钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
            </ul>
        </section>

        <section id="qrcode" class="flex flex-justify-center"></section>
        <h3 class="notice">扫描二维码快速交易</h3>

        <passwordPanel 
          :setSwitch="passWordSwitch" 
          :settingPasswordSwitch="true" 
          :secondValid="false" 
          v-on:hidePassword="hidePassword" 
          v-on:callBack ="submitData">
        </passwordPanel>
    </div>
</template>

<style lang="scss" scoped>
.green-color {
  color: green;
}

#deal-detail {
  background: #eee;
  min-height: 100vh;
  padding-top: 2em;
}

.big-winner-tip {
  width: 4em;
  height: 4em;
  border-radius: 50%;
  background: #26a2ff;
  position: absolute;
  right: 2em;

  p {
    text-align: center;
    font-size: 0.9em;
    color: #fff;
  }
}

.pay-wrap {
  .pay-money {
    background: #fff;
    width: 90%;
    border-radius: 0.2em;
    height: 2.5em;

    label {
      width: 40%;
      padding-left: 0.5em;
      padding-right: 0.5em;
      box-sizing: border-box;
    }

    .input-wrap {
      height: 100%;
      width: 60%;
      input {
        box-sizing: border-box;
        font-size: 1em;
        padding-left: 0.5em;
        width: 100%;
        border: none;
        outline: none;
        height: 100%;
      }
    }
  }

  .get-money {
    @extend .pay-money;
    margin-top: 1em;
  }
}

.mint-button {
  margin-top: 1em;
  width: 90%;
}

.pay-record {
  padding-top: 0.5em;
  .title {
    width: 90%;
    height: 4.5em;
    line-height: 2em;
    background: #fff;
    margin: 0 auto;

    .top {
      height: 2em;
      width: 100%;
      padding-left: 0.5em;
      box-sizing: border-box;
      span {
        font-size: 1em;
        color: #555;
      }
    }

    .bottom {
      width: 100%;
      height: 3.5em;
      img {
        width: 2em;
        height: 2em;
        display: block;
        margin-left: 0.5em;
      }
    }

    .info-friend {
      margin-right: 0.5em;
      background: green;
      color: #fff;
      padding-left: 0.3em;
      padding-right: 0.3em;
      border-radius: 0.3em;
      font-size: 0.9em;
    }
  }

  ul {
    margin-top: 0.5em;
    li {
      margin-top: 0.2em;
      width: 90%;
      overflow-x: hidden;
      .slider-item {
        box-sizing: border-box;
        padding-left: 0.5em;
        padding-right: 0.5em;
        height: 3em;

        .pay-money-text {
          width: 20%;
          height: 100%;
          .money {
            font-size: 1.1em;
            width: 100%;
            line-height: 2em;
            height: 50%;
          }

          .title {
            font-size: 0.9em;
            height: 50%;
            width: 100%;
            background: #fff;
          }
        }

        img {
          width: 2.5em;
          height: 2.5em;
          display: block;
        }

        span {
          display: block;
          text-align: center;
        }
      }
      /*#slider-component {
        margin-top: 0.5em;
      }*/
    }
  }
}

#qrcode {
  margin-top: 1.5em;
}

.notice {
  margin-top: 1em;
  padding-bottom: 1.5em;
  text-align: center;
  font-size: 0.9em;
  color: #999;
}
</style>


<script>
import topBack from "../../components/topBack";
import slider from "../../components/slider";
import dealContent from "./dealContent";
import passwordPanel from "../../components/password";
import request from "../../utils/userRequest";
import {Toast} from "mint-ui"

import Loading from "../../utils/loading";

import qrCode from "../../utils/qrCode";

export default {
  name: "makeDealDetail",
  components: { topBack, slider, dealContent, passwordPanel },

  data() {
    return {
      passWordSwitch: false,
      renderData: {
        name: null
      },
      moneyData: {
        payMoney: null,
        getMoney: null
      },
      payType: null,    // 支付方式，取钱get 放钱put
      transfer_id:"",    // 交易id
      password:""
    };
  },
  created() {
    this.init();
  },
  mounted() {
    this._getQRCode();
  },
  methods: {
    deleteIt() {
      console.log("i m ru");
    },

    init() {
      Loading.getInstance().open();
      this.transfer_id = this.$route.query.id;
      var _data = {
        transfer_id: this.transfer_id
      };
      request
        .getInstance()
        .getData("api/transfer/show" + "?transfer_id=" + this.transfer_id)
        .then(res => {
          console.log(res);

          this.renderData = res.data.data;
          Loading.getInstance().close();
        })
        .catch(err => {
          console.error(err);
        });
    },

    goTipPage() {
      this.$router.push("/makeDeal/deal_tip" + "?id=" + this.transfer_id);
    },

    showPassword() {
      this.passWordSwitch = true;
    },
    hidePassword() {
      this.passWordSwitch = false;
    },

    callPassword(){

      if(this.payType == "put"){
        this.showPassword();
      }else if(this.payType == "get"){
        this.submitData();
      }else {
        Toast("请填写拿钱数额或取钱数额");
      }
    },

    // 提交交易  拿钱或者付钱
    submitData(password){
      // 放钱
      if(this.payType == "put"){
        var _data = {
          transfer_id :this.transfer_id,
          points :this.moneyData.payMoney,
          action :"put",
          pay_password:password
        }

        request.getInstance().postData("api/transfer/trade",_data).then(res=>{
          console.log(res);
          this.init();
        }).catch(err=>{
          console.error(err);
        });

        this.hidePassword();

      }else if(this.payType == "get"){
        // 拿钱
        var _data = {
          transfer_id :0,
          points :0,
          action :"put",
        }
      }
    },

    getResult(result) {
      console.log(result);
      this.password = result;
    },
    _getQRCode: function() {
      var qrcode = new QRCode(document.getElementById("qrcode"), {
        width: 100, //设置宽高
        height: 100
      });

      qrcode.makeCode("http://www.baidu.com");
    }
  },
  watch: {
    "moneyData.payMoney": function() {
      // 放钱
      this.moneyData.getMoney = null;
      this.payType = "put";
    },
    "moneyData.getMoney": function() {
      // 拿钱
      this.moneyData.payMoney = null;
      this.payType = "get";
    }
  }
};
</script>

