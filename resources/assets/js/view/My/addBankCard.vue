<template>
  <div id="addBankCard">
    <topBack title="添加银行卡"></topBack>
    <div class="addBankCard-box">
      <h2>请绑定持卡人本人的银行卡</h2>
      <div class="flex flex-v flex-justify-center">
        <section class="account-container">
          <div class="account-box flex flex-align-center">
            <span>姓名:</span>
            <em class="flex-1 number">{{name}}</em>
          </div>
          <div class="account-box flex flex-align-center">
            <span>身份证号:</span>
            <em class="flex-1 number">{{id_number}}</em>
          </div>
        </section>
      </div>
      <div class="bank-info flex flex-v flex-justify-center">
        <mt-field label="所属银行" placeholder="请选择银行卡所属银行" type="text"></mt-field>
        <mt-field label="银行卡号" placeholder="请填写银行卡号" type="number"></mt-field>
      </div>
      <div class="bank-info flex flex-v flex-justify-center">
        <mt-field label="预留手机号" placeholder="请填写银行卡预留手机号" type="number"></mt-field>
      </div>
    </div>
    <a href="javascript:;" class="btn">
      <mt-button type="primary" size="large">下一步</mt-button>
    </a>
  </div>
</template>


<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack";

  import Loading from '../../utils/loading'

  export default {
    data() {
      return {
        name: null,
        id_number: null
      }
    },
    components: { topBack },
    created() {
      this.personalInfo();
    },
    methods: {
      //个人信息
      personalInfo() {
        Loading.getInstance().open("加载中...");

        request.getInstance().getData("api/my/info")
          .then((res) => {
            this.name = res.data.data.name;
            this.id_number = res.data.data.id_number;
            Loading.getInstance().close();
          })
          .catch((err) => {
            console.error(err);
          })
      },
    }
  };
</script>

<style lang="scss" scoped>
  #addBankCard {
    background: #eee;
    height: 100vh;
    padding-top: 2em;
  }

  .addBankCard-box {
    border-top: 1px solid #ccc;
    h2 {
      color: #999;
      height: 2em;
      line-height: 2em;
      padding-left: 10px;
      padding-top: 0.7em;
    }
    .bank-info {
      margin-top: 1em;
    }
  }

  .btn {
    display: block;
    margin-top: 1em;
    width: 90%;
    margin: auto;
    margin-top: 2em;
  }
  .account-container {
    background: #fff;
    padding-left: 10px;
    .account-box {
      width: 100%;
      height: 3em;
      border-top: 1px solid #d9d9d9;
      span {
        display: inline-block;
        width: 105px;
      }
      .number {
        color: #666;
        font-size: inherit;
      }
    }
  }
</style>