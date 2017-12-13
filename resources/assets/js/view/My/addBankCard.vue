<template>
  <div id="addBankCard">
    <topBack title="添加银行卡"></topBack>
    <div class="addBankCard-box">
      <h2>请绑定持卡人本人的银行卡</h2>
      <div class="flex flex-v flex-justify-center">
        <mt-field label="姓名" placeholder="请输入姓名" type="text"></mt-field>
        <mt-field label="身份证号" placeholder="请输入身份证号" type="text"></mt-field>
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
import topBack from "../../components/topBack";

export default {
  components: { topBack }
};
</script>

<style lang="scss" scoped>
  #addBankCard{
    background: #eee;
    height: 100vh;
    padding-top:2em;
  }
  .addBankCard-box{
    border-top: 1px solid #ccc;
    h2{
      color:#999;
      height: 2em;
      line-height: 2em;
      padding-left: 10px;
      padding-top:0.7em;
    }
    .bank-info{
      margin-top: 1em;
    }
  }
  .btn{
    display: block;
    margin-top: 1em;
    width: 90%;
    margin:auto;
    margin-top: 2em;
  }
  
</style>
