<template>
  <div id="bankManage">
    <topBack title="银行卡管理"></topBack>
    <div class="bankCard-container">
      <ul class="bankCard-list" v-for="item in bankList">
        <li>
          <div class="bankCard-box flex">
            <div class="card-image">
              <img src="/images/personal.jpg">
            </div>
            <div class="card-info">
              <div class="bank-name">{{item.bank}}</div>
              <div class="card-type">{{item.card_type}}</div>
              <div class="card-number">{{item.card_num}}</div>
            </div>
          </div>
          <button class="del" @click="del(item.card_id)">
            <i class="iconfont">&#xe634;</i>
          </button>
          <div class="binding">{{item.is_pay_card? '结算卡' : '不是结算卡' }}</div>
        </li>
      </ul>
      <div class="add-bankCard" @click="showPassword">
        <a href="javascript:;">添加新银行卡</a>
      </div>
    </div>
    <passWorld :setSwitch="showPasswordTag" v-on:hidePassword="hidePassword" v-on:callBack="callBack"></passWorld>
  </div>
</template>

<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack";
  import passWorld from "../../components/password"
  import { MessageBox,Toast } from "mint-ui";

  import Loading from '../../utils/loading'

  export default {
    components: { topBack, passWorld },
    data() {
      return {
        bankList: [],
        showPasswordTag: false,       // 密码弹出开关
        isdel: false
      }
    },
    created(){
      this.bank();
    },
    methods: {
      showPassword() {
        request.getInstance().getData('api/my/info')
          .then((res) => {
           if(res.data.data.has_pay_password==0){
              //调转到设置支付密码
              this.$router.push('/my/setting_password');
           }else{
            this.showPasswordTag = true;   //密码层弹出

           }
          })
          .catch((err) => {
            console.log(err);
          })
      },
      //支付密码验证
      payPasswordVal(){

      },
      hidePassword() {
        this.showPasswordTag = false;
      },
      //银行卡列表
      bank:function(){
        Loading.getInstance().open("加载中...");
        
        request.getInstance().getData('api/card/index')
          .then((res) => {
            this.bankList = res.data.data;
            Loading.getInstance().close();
          })
          .catch((err) => {
            console.log(err);
          })
      },
      //删除银行卡
      del(card_id) {
        MessageBox.confirm("是否删除该银行卡?", "温馨提示").then(
          () => {
            request.getInstance().postData("api/card/delete?card_id=" + card_id)
            .then((res) => {
              Toast({
                message: "删除成功",
                duration: 800
              });
              this.bank();
            })
            .catch((err) => {
              console.log(err);
            })
          },
          () => {
            //取消操作
            console.log("已经取消");
          }
        );
      },
      //支付密码验证
      callBack(password){
        var temp = {};
        temp.password=password;
        
        request.getInstance().postData('api/my/pay_password',temp)
          .then((res) => {
            if(res.data.code==1){
              this.$router.push('/my/bankCardManage/addBankCard');
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
  #bankManage {
    padding-top: 2em;
    box-sizing: border-box;
  }

  .bankCard-container {
    width: 100;
    border-top: 1px solid #ccc;
    padding-top: 1em;
  }

  .bankCard-list {
    width: 90%;
    margin: auto;
    li {
      border: 1px solid #ccc;
      margin-bottom: 1em;
      padding: 0.7em;
      position: relative;
      .del,
      .binding {
        position: absolute;
        right: 1em;
      }
      .del {
        bottom: 10px;
        background: #fff;
        border: none;
        outline: none;
        i {
          font-size: 2em;
          color: #777;
        }
      }
      .binding {
        top: 1em;
        color: #333;
        font-size: 0.8em;
      }
    }
  }

  .bankCard-box {
    .card-image {
      width: 3em;
      height: 3em;
      >img {
        display: block;
        width: 100%;
        border-radius: 50%;
      }
    }
    .card-info {
      margin-left: 1em;
      .card-type,
      .bank-name {
        margin-bottom: 0.3em;
      }
      .card-type,
      .card-number {
        color: #999;
        font-size: 0.9em;
      }
      .bank-name {
        font-size: 1em;
        margin-top: 0.1em;
      }
      .card-number {
        font-size: 1em;
      }
    }
  }

  .add-bankCard {
    width: 90%;
    height: 4em;
    line-height: 4em;
    border: 1px dashed #ccc;
    text-align: center;
    margin: auto;
    margin-top: 1em;
    a {
      display: block;
      width: 100%;
      color: #999;
    }
  }
</style>