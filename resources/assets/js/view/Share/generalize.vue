<template>
  <div id="share">
    <topBack title="二维码推广" style="background:#38C3EC;color:#fff;"></topBack>
    <h2>服务于百万用户的聚合收付款工具</h2>
    <div class="reffer-box">
      <div class="reffer-image">
        <img src="/images/avatar.jpg">
      </div>
      <div class="reffer">推荐人:<span>刘**</span></div>
      <div class="code-box">
          <div class="code">
            <img src="/images/code.png">
          </div>
          <div>打开微信扫一扫</div>
      </div>
     
    </div>
  </div>
</template>

<style lang="scss" scoped>
  #share {
    box-sizing: border-box;
    padding-top: 2em;
    height: 100vh;
    width: 100%;
    background: #fff;
    h2{
      font-weight:700;
      font-size:1.5em;
      margin:1em auto;
      width:60%;
      text-align:center;
      line-height:1.2em;
    }
  }
  .reffer-box{
    width: 100%;
    margin:0 auto;
    margin-top:10em;
    text-align: center;
    .reffer-image{
      margin-bottom:0.2em;
      img{
        width:4em;
        height:4em;
        border-radius:50%;
      }
    }
    .reffer{
      margin-top:0.2em;
    }
  }
  .code-box{
    margin-top:0.5em;
    .code{
      margin-bottom:0.2em;
    }
  }
</style>

<script>
  import topBack from "../../components/topBack";
  import request from "../../utils/userRequest"
  import Loading from "../../utils/loading"
  import moment from 'moment'
  import { Toast } from 'mint-ui'

  export default {
    created() {
    },

    data() {
      return {


      }
    },
    methods: {
     
    },
    components: { topBack},
  }
</script>