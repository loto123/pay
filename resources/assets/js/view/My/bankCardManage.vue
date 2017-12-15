<template>
  <div id="bankManage">
    <topBack title="银行卡管理"></topBack>
    <div class="bankCard-container">
      <ul class="bankCard-list" v-for="item in bankList" >
        <li>
          <div class="bankCard-box flex">
            <div class="card-image">
              <img src="/images/personal.jpg" alt="">
            </div>
            <div class="card-info">
              <div class="bank-name">{{item.bank}}</div>
              <div class="card-type">{{item.card_type}}</div>
              <div class="card-number">{{item.card_num}}</div>
            </div>
          </div>
          <button class="del" @click="del">
            <i class="iconfont">&#xe634;</i>
          </button>
          <div class="binding">(已绑定)</div>
        </li>
      </ul>
      <div class="add-bankCard">
        <a href="/#/my/bankCardManage/addBankCard">添加新银行卡</a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import topBack from "../../components/topBack";
import { MessageBox } from "mint-ui";
import { Toast } from "mint-ui";
import request from '../../utils/userRequest';
export default {
  components: { topBack },
  data () {
    return {
      bankList:[]
    }
  },
  created:function(){
      var _this=this;
      request.getInstance().getData('api/card/index')
      .then((res) => {
        console.log(res);
        _this.bankList=res.data.data;
      })
      .catch((err) => {
        console.log(err);
      })
  },
  methods: {
    del() {
      MessageBox.confirm("是否删除该银行卡?", "温馨提示").then(
        () => {
          Toast({
            message: "删除成功",
            iconClass: "icon icon-success",
            duration: 800
          });
        },
        () => {
          //取消操作
          console.log("已经取消");
        }
      );
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
    > img {
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
