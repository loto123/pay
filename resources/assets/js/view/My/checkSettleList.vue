<template>
  <div id="bankManage">
    <topBack title="更换结算卡"></topBack>
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
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack";
  import { MessageBox,Toast } from "mint-ui";

  import Loading from '../../utils/loading'

  export default {
    components: { topBack },
    data() {
      return {
        bankList: []
      }
    },
    created(){
      this.bank();
    },
    methods: {
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
</style>