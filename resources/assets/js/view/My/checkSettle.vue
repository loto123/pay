<template>
  <div id="settleInfo">
    <topBack title="结算卡信息"></topBack>
    <div class="settleInfo-container">
      <div class="account flex flex-align-center">
        <div class="account-title">账号</div>
        <div class="account-number">{{userMobile}}</div>
      </div>
      <ul class="info-content-list">
        <li>
          <div class="content-box flex flex-align-center">
            <div class="title">真实姓名</div>
            <div class="content">{{realName}}</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">身份证号码</div>
            <div class="content">{{idCard}}</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">银行卡号(储蓄卡)</div>
            <div class="content">{{bankCard}}</div>
          </div>
          <div class="content-box flex flex-align-center">
            <div class="title">所属银行</div>
            <div class="content">{{bankName}}</div>
          </div>
        </li>
      </ul>
      <a href="javascript:;" class="btn" @click = "showPassword">
        <mt-button type="primary" size="large">更换结算卡</mt-button>
      </a>
    </div>

    <passWorld :setSwitch="showPasswordTag" v-on:hidePassword = "hidePassword" v-on:callBack="callBack"></passWorld>
  </div>
</template>

<script>
import topBack from "../../components/topBack";
import passWorld from "../../components/password"

import request from '../../utils/userRequest';
import { Toast } from "mint-ui";
export default {
  components: { topBack , passWorld},
  data(){
    return {
      showPasswordTag:false,       // 密码弹出开关
      userMobile:null,
      realName:null,
      idCard:null,
      bankCard:null,
      bankName:null
    }
  },
  created(){
    this.getData();
  },
  methods:{
    showPassword(){
      this.showPasswordTag = true;
    },

    hidePassword(){
      this.showPasswordTag = false;
    },
    getData(){
      var _this=this;
      request.getInstance().getData('api/my/getPayCard').then((res) => {
        console.log(res);
        //   this.$router.push('/my/checkSettle');
        this.userMobile=res.data.data.user_mobile
        this.realName=res.data.data.holder_name
        this.idCard=res.data.data.holder_id
        this.bankCard=res.data.data.card_num
        this.bankName=res.data.data.bank
      }).catch((err) => {
        Toast({
            message: err.data.msg,
            duration: 800
          });
        this.$router.go(-1);
      })
    },
     //支付密码验证
     callBack(password){
      var temp = {};
      temp.password=password;
      
      request.getInstance().postData('api/my/pay_password',temp)
        .then((res) => {
          if(res.data.code==1){
            this.$router.push('/my/checkSettle/list');
          }
        })
        .catch((err) => {
          console.error(err.data.msg);
        })
    }
  }
};
</script>

<style lang="scss" scoped>
#settleInfo {
  background: #eee;
  height: 100vh;
  padding-top: 2em;
  box-sizing:border-box;
}
.account {
  height: 3em;
  line-height: 3em;
  background: #fff;
  padding-left: 1em;
  margin-bottom: 1em;
  border: 1px solid #ccc;
  margin-bottom: 1em;
  .account-title {
    width: 8em;
    margin-right: 1em;
    color: #999;
    font-size: 0.8em;
  }
  .account-number {
    font-size: 0.8em;
  }
}

.info-content-list {
  margin-bottom: 3em;
  li {
    background: #fff;
    padding: 0 1em;
    border: 1px solid #ccc;
    .content-box {
      height: 3em;
      line-height: 3em;
      border-bottom: 1px solid #d9d9d9;
      &:nth-child(2) {
        border-bottom: 1px solid #aaa;
      }
      &:last-child {
        border-bottom: none;
      }
      .title {
        width: 8em;
        margin-right: 1em;
        color: #999;
        font-size: 0.8em;
      }
      .content {
        font-size: 0.8em;
      }
    }
  }
}
</style>
