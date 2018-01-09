<template>
  <div id="billDetails">
    <topBack title="详情"></topBack>
    <div class="details-content">
      <div class="money-box">
        <span>入账金额</span>
        <em>{{amount}}</em>
      </div>
      <ul class="billDetails-list">
        <li>
          <div class="title">类型</div>
          <div class="content">{{type}}</div>
        </li>
        <li>
          <div class="title">时间</div>
          <div class="content">{{time}}</div>
        </li>
        <li>
          <div class="title">交易单号</div>
          <div class="content">{{transfer_id}}</div>
        </li>
        <li>
          <div class="title">分润来源</div>
          <div class="content flex flex-align-center">
            <div>{{mobile}}</div>
            <div class="personal-img">
              <img src="/images/avatar.jpg" width="40" height="40">
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
  import topBack from "../../components/topBack.vue";
  export default {
    data() {
      return {
        amount:null,
        mobile:null,
        thumb:null,
        time:null,
        transfer_id:null,
        type:null
      }
    },
    created() {
      this.details();
    },
    methods: {
      details() {
        var _temp = {};
        _temp.notice_id = this.$route.query.notice_id;
        request.getInstance().postData('api/notice/detail', _temp)
          .then((res) => {
            this.amount=res.data.data.amount;
            this.mobile=res.data.data.mobile;
            this.thumb=res.data.data.thumb;
            this.time=res.data.data.time;
            this.transfer_id=res.data.data.transfer_id;
            this.type=res.data.data.type;
          })
          .catch((err) => {
            Toast(err.data.msg);
          })
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
        img{
          border-radius:50%;
          margin-left:0.5em;
        }
      }
    }
  }
</style>