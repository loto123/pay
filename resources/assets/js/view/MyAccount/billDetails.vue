<template>
  <div id="billDetails">
    <topBack title="账单明细"></topBack>
    <div class="details-content">
      <div class="money-box" v-if="(this.type==0||this.type==1)&&this.curType==1">
        <span>{{this.mode == 0?'支出':'收入'}}</span>
        <em v-bind:class="[mode==1?'active':'']">{{this.mode == 1?'+'+amount:-amount}}</em>
      </div>
      <div class="money-box" v-if="(this.type==0||this.type==1||this.type==2||this.type==3||this.type==4||this.type==5||this.type==6||this.type==8||this.type==9||this.type==10)&&this.curType==2">
        <span>{{this.mode == 0?'收入':'支出'}}钻石</span>
        <em v-bind:class="[mode==1?'':'active']">{{this.mode == 1?-amount:'+'+amount}}</em>
      </div>
      <ul class="billDetails-list">
        <li>
          <div class="title">类型</div>
          <div v-if="(this.type==0||this.type==1)&&this.curType==1">
            <div class="content">{{this.mode==1?'收入':'支出'}}</div>
          </div>
          <div v-if="(this.type==0||this.type==1||this.type==2||this.type==3||this.type==4||this.type==5||this.type==6||this.type==8||this.type==9||this.type==10)&&this.curType==2">
            <div class="content" >{{this.mode==0?'收入':'支出'}}</div>
          </div>
        </li>
        <li>
          <div class="title">时间</div>
          <div class="content">{{changeTime(created_at)}}</div>
        </li>
        <li v-if="this.type==2||this.type==3||this.type==6||this.type==8||this.type==10">
          <div class="title">任务单号</div>
          <div class="content">{{no}}</div>
        </li>
        <li v-else>
          <div class="title">单号</div>
          <div class="content">{{no}}</div>
        </li>
        <li>
          <div class="title">备注</div>
          <div class="content">{{status(this.type)}}</div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import Loading from '../../utils/loading'
  import { MessageBox, Toast } from "mint-ui";
  export default {
    data() {
      return {
        showAlert: false,
        created_at: null,	//时间
        remark: null,		//备注
        type: null,			//类型
        no: null,			//交易单号
        amount: null,		//入账
        mode: null,			//0:收入		1:支出
        isBuy: false,
        curType: null
      };
    },
    created() {
      this.init();
    },
    methods: {
      init() {
        Loading.getInstance().open("加载中...");
        var self = this;
        var _id = this.$route.query.id;
        request.getInstance().getData("api/account/records/detail/" + _id)
          .then((res) => {
            this.remark = res.data.data.remark
            this.no = res.data.data.no
            this.created_at = res.data.data.created_at
            this.amount = res.data.data.amount
            this.type = res.data.data.type
            this.mode = res.data.data.mode
            Loading.getInstance().close();
          })
          .catch((err) => {
            Loading.getInstance().close();
          })
        this.curType = this.$route.query.types;
      },
      monthData() {
        Loading.getInstance().open("加载中...");
        var self = this;
        _data = {
          month: this.month
        }
        request.getInstance().getData("api/account/records/month", _data)
          .then((res) => {
            Loading.getInstance().close();
          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })
      },
      changeTime(shijianchuo) {
        function add0(m) { return m < 10 ? '0' + m : m }

        var time = new Date(shijianchuo * 1000);
        var y = time.getFullYear();
        var m = time.getMonth() + 1;
        var d = time.getDate();
        var h = time.getHours();
        var mm = time.getMinutes();
        var s = time.getSeconds();
        return y + '-' + add0(m) + '-' + add0(d) + ' ' + add0(h) + ':' + add0(mm) + ':' + add0(s);
      },
      status(type) {
        let result = '';
        switch (type) {
          case 0: result = '购买'; break;
          case 1: result = '出售'; break;
          case 2: result = '任务拿钻'; break;
          case 3: result = '任务交钻'; break;
          case 4: result = '转账到公会'; break;
          case 5: result = '公会转入'; break;
          case 6: result = '任务手续费'; break;
          case 8: result = '任务加速'; break;
          case 9: result = '拿钻撤销'; break;
          case 10: result = '分润'; break;
        }
        return result;
      }
    },
    components: {
      topBack
    }
  };
</script>

<style lang="scss" scoped>
  @import "../../../sass/oo_flex.scss";
  #billDetails {
    padding-top: 2em;
    background: #eee;
    height: 100vh;
    box-sizing: border-box;
  }

  .details-content {
    background: #fff;
    padding-bottom: 7em;
  }

  .money-box {
    height: 40px;
    line-height: 40px;
    border-bottom: 1px solid #ccc;
    display: flex;
    justify-content: space-between;
    padding: .5em .7em 0 .7em;
    .active {
      color: #00cc00;
    }
  }

  .billDetails-list {
    li {
      margin-top: 1em;
      display: flex;
      justify-content: space-between;
      padding: 0 0.7em;
      .title {
        color: #333;
      }
      .content {
        color: #555;
      }
      .remark {
        width: 20px;
      }
    }
  }
</style>