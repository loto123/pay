<template>
  <div id="withdraw" class="withdraw-container">
    <topBack title="提钻到个人账户">
      <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
        <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
      </div>
    </topBack>
    <div class="withdraw-box">
      <div class="title">体钻</div>
      <div class="withdraw-money flex flex-justify-center">
        <label><i class="diamond">&#xe6f9;</i></label>
        <input type="text" placeholder="请输入钻石数量" v-model="amount">
      </div>
      <div class="all-money flex">
        <div class="money">
          可提现钻石数量 <span>{{balance}}<i class="diamond">&#xe6f9;</i></span>,
        </div>
        <a href="javascript:;" class="all-withdraw" @click="allWithdraw">全部提钻</a>
      </div>
      <a href="javascript:;" class="withdraw-btn" @click="withdrawBtn">
        <mt-button type="primary" size="large">提现</mt-button>
      </a>
    </div>
    <passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
  </div>
</template>

<script>
  import request from '../../utils/userRequest';
  import topBack from '../../components/topBack.vue'
  import passWorld from "../../components/password"
  import Loading from '../../utils/loading'
  import { MessageBox, Toast } from "mint-ui";

  export default {
    data() {
      return {
        balance: null,//可用余额
        showPasswordTag: false,       // 密码弹出开关

        amount: null,	//提现金钱
        options1: [],
        value: null,
        has_pay_password: null,//是否设置支付密码
        shopId:null
      }
    },
    created() {
      this.init();
    },
    components: { topBack, passWorld },

    methods: {
      goIndex() {
        this.$router.push('/index');
      },
      hidePassword() {
        this.showPasswordTag = false;
      },
      init() {
        Loading.getInstance().open("加载中...");
          this.shopId = this.$route.query.shopId;

          Promise.all([request.getInstance().getData("api/account"), request.getInstance().getData("api/profit/balance")])
          .then(res=>{
            this.has_pay_password = res[0].data.data.has_pay_password;
            this.balance = res[1].data.data.profit;
            Loading.getInstance().close();
          }).catch(err=>{
            Loading.getInstance().close();
            Toast(err.data.msg);
          });
      },
      allWithdraw() {
        this.amount = this.balance;
      },
      withdrawBtn() {
        var self = this;
        //成功内容
        var _data = {
          amount: this.amount
        }

        if (this.amount<=0) {
          Toast('请输入提现金额');
          return
        }else if (this.amount>this.balance) {
          Toast('余额不足');
          return
        }
        if (this.has_pay_password == 0) {
          this.$router.push('/my/setting_password');//跳转到设置支付密码
        } else {
          this.showPasswordTag = true;   //密码层弹出
        }
      },
      //支付密码验证
      callBack(password) {

        var temp = {};
        temp.password = password;
        this.shopId = this.$route.query.shopId;

        var _data = {
          amount: this.amount,
          password: password
        }

        Promise.all([request.getInstance().postData('api/my/pay_password', temp), request.getInstance().postData('api/profit/withdraw' , _data)])
          .then((res) => {
            Toast('提现成功');
            this.$router.go(-1);
            // this.$router.push('/shop/shopAccount?id='+this.shopId);

          })
          .catch((err) => {
            Toast(err.data.msg);
          });

      }
    }
  };
</script>

<style lang="scss" scoped>
  .withdraw-container {
    background: #eee;
    height: 100vh;
    padding-top: 2em;
    box-sizing: border-box;
  }

  .withdraw-box {
    background: #fff;
    padding: 1em;
    margin: 0 0.5em;
    .tltle {
      font-size: 1em;
      color: #999;
    }
  }

  .withdraw-money {
    border-bottom: 1px solid #ccc;
    vertical-align: middle;
    margin-top: 2em;
    font-size: 1.2em;
    padding: 0.2em 0;
    input {
      border: none;
      outline: none;
      width: 100%;
      font-size: 0.9em;
    }
  }

  .all-money {
    margin-top: 1em;
    font-size: 1em;
    .money {
      color: #666;
    }
    .all-withdraw {
      color: #199ed8;
      margin-left: 0.4em;
    }
  }
  .withdraw-btn {
    display: block;
    margin-top: 3em;
    margin-bottom: 1em;
  }
</style>