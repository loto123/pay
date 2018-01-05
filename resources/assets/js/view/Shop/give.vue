<template>
  <div id="give" class="give-container">
    <topBack title="转账给店铺成员">
      <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
        <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
      </div>
    </topBack>

    <div class="user-box-wrap flex flex-align-center flex-justify-center">
      <div class="user-box flex flex-v flex-align-center">
        <img :src="transferData.avatar" alt="">
        <h3 class="name">{{transferData.name}}</h3>
        <h3 class="id">{{transferData.id}}</h3>
      </div>
    </div>

    <div class="give-box">
      <div class="title">转账金额</div>
      <div class="give-money flex flex-justify-center">
        <label>￥</label>
        <input type="text" placeholder="请输入金额" v-model="amount">
      </div>
      <div class="all-money flex">
        <div class="money">可转账余额 ¥
          <span>{{balance}}</span>, </div>
        <a href="javascript:;" class="all-giveAcc" @click="allGive">全部转账</a>
      </div>

      <div class="comment flex flex-align-center flex-justify-center">
        <textarea name="" id="" cols="30" rows="10" placeholder="添加备注（50字以内）" maxlength="50" v-model="comment"></textarea>
      </div>

      <a href="javascript:;" class="transAcc-btn">
        <mt-button type="primary" size="large" @click="submitDate">转账</mt-button>
      </a>
    </div>

    <choiseMember :isShow="choiseMemberSwitch" v-on:hide="hideMemberChoise" :dataList="memberList" v-on:submit="getMemberData"
      :singleMode="true" :backUrl="true">
    </choiseMember>
    <passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
  </div>


</template>

<script>
  import topBack from "../../components/topBack.vue";
  import choiseMember from "../MakeDeal/choiseMember.vue"
  import passWorld from "../../components/password"
  import Loading from "../../utils/loading.js"
  import request from "../../utils/userRequest.js"
  import { Toast } from "mint-ui"

  export default {
    created() {
      this.init();
      this.getMoney();
    },

    data() {
      return {
        choiseMemberSwitch: true,
        memberList: [],
        shopId: null,
        transferData: {},
        amount: null,                      // 转账金额
        comment: "",
        balance: null,
        showPasswordTag: false,       // 密码弹出开关
        has_pay_password: null,//是否设置支付密码
      };
    },
    components: { topBack, choiseMember, passWorld },
    methods: {

      goIndex() {
        this.$router.push("/index");
      },
      getMemberData(data) {
        this.transferData = data;
      },
      hideMemberChoise(e) {
        if (e) {
          this.$router.go(-1);
        }
        this.choiseMemberSwitch = false;
      },
      showMemberChoise() {
        this.choiseMemberSwitch = true;
      },
      submitDate() {
        // /shop/transfer/{shop_id}/{user_id}
        if (this.amount <= 0) {
          Toast("请输入转账金额");
          return
        } else if (this.amount > this.balance) {
          Toast("余额不足");
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
        var _data = {
          amount: this.amount,
          remark: this.comment,
          password: password
        }
        Loading.getInstance().open();
        Promise.all([request.getInstance().postData('api/my/pay_password', temp), request.getInstance().postData('api/shop/transfer/' + this.shopId + "/" + this.transferData.id, _data)])
          .then((res) => {
            Loading.getInstance().close();
            Toast("转账成功");
            this.$router.push('/shop/shopAccount?id=' + this.shopId);
          })
          .catch((err) => {
            Loading.getInstance().close();
            Toast(err.data.msg);
          })
      },
      init() {
        Loading.getInstance().open();
        this.memberList = [];
        this.shopId = this.$route.query.shopId;
        request.getInstance().getData("api/shop/members/" + this.shopId)
          .then(res => {

            for (let i = 0; i < res.data.data.members.length; i++) {
              var _temp = res.data.data.members[i];
              _temp.checked = false;
              this.memberList.push(_temp);
            }

            this.memberList = res.data.data.members;
            Loading.getInstance().close();
          })
          .catch();
      },
      getMoney() {
        Loading.getInstance().open('加载中...');

        this.shopId = this.$route.query.shopId;
        Promise.all([request.getInstance().getData("api/account"), request.getInstance().getData("api/shop/account/" + this.shopId)])
          .then(res => {
            this.has_pay_password = res[0].data.data.has_pay_password;
            this.balance = res[1].data.data.balance;
            Loading.getInstance().close();
          }).catch(err => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          });
      },
      allGive() { //全部转账
        this.amount = this.balance;
      },
    }
  };
</script>

<style lang="scss" scoped>
  .give-container {
    background: #eee;
    height: 100vh;
    padding-top: 2em;
    box-sizing: border-box;
  }

  .user-box-wrap {
    padding-top: 1em;
    height: 6em;
    background: #fff;
    margin: 0 0.5em;

    .user-box {
      width: 5em;
      height: 100%;

      >img {
        width: 4em;
        height: 4em;
        border-radius: 50%;
      }

      .name {
        font-size: 0.95em;
        padding-top: 0.2em;
        padding-bottom: 0.2em;
      }

      .id {
        font-size: 0.9em;
        padding-top: 0.2em;
        padding-bottom: 0.2em;
      }
    }
  }

  .give-box {
    background: #fff;
    padding: 1em;
    margin: 0 0.5em;
    .tltle {
      font-size: 1em;
      color: #999;
    }
  }

  .give-money {
    border-bottom: 1px solid #ccc;
    vertical-align: middle;
    margin-top: 1em;
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
    .money {
      color: #666;
    }
    .all-giveAcc {
      color: #199ed8;
      margin-left: 0.4em;
    }
  }

  .comment {
    width: 100%;
    height: 5em;
    box-sizing: border-box;
    border: 1px solid #eee;
    border-radius: 0.4em;
    margin-top: 1em;

    >textarea {
      width: 90%;
      height: 90%;
      border: none;
      outline: none;
      display: block;
      resize: none;
      font-size: 1.5em;
    }
  }

  .give-store {
    margin-top: 2.5em;
    h3 {
      color: #999;
      font-size: 0.9em;
    }
    #store {
      margin-top: 0.5em;
      width: 100%;
      height: 40px;
      line-height: 40px;
      border: 1px solid #ccc;
    }
  }

  .transAcc-btn {
    display: block;
    margin-top: 1em;
    margin-bottom: 1em;
  }
</style>