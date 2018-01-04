<template>
  <div id="share">
    <topBack title="二维码推广" style="background:#38C3EC;color:#fff;"></topBack>
    <div class="back-img">
        <a href="javascript:;" class="regist-btn" id="regist" @click="regist">立即注册</a>
        <a href="javascript:;" class="download-btn" id="downloadBtn">下载APP</a>
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
  }
  .back-img {
    background: url(/images/share2.jpg) no-repeat;
    position: relative;
    width: 100%;
    height: 100%;
    background-size: 100% 100%;
    a{
      background: #38C3EC;
      color: #fff;
      width: 12em;
      height: 2.5em;
      line-height: 2.5em;
      position: absolute;
      left: 0;
      right: 0;
      margin: 0 auto;
      text-align: center;
      border-radius: 5px;
    }
  }

  .regist-btn {
    bottom: 4.5em;
  }
  .download-btn{
    bottom: 1em;
  }
  
</style>

<script>
  import topBack from "../../components/topBack";
  import request from "../../utils/userRequest"
  import Loading from "../../utils/loading"
  import moment from 'moment'
  import { Toast } from 'mint-ui'

  export default {
    data() {
      return {

      }
    },
    methods: {
      regist(){
        this.$router.push('/login/regist');
      }
    },
    components: { topBack},
  }
</script>