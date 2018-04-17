<template>
  <div id="share">
    <div class="back-img">
      <a href="javascript:;" class="regist-btn" id="regist" @click="regist">立即注册</a>
      <a href="javascript:;" class="download-btn" id="downloadBtn">下载APP</a>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  #share {
    box-sizing: border-box;
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
    a {
      background: #38C3EC;
      color: #fff;
      width: 13em;
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

  .download-btn {
    bottom: 1em;
  }
</style>

<script>
  import topBack from "../../components/topBack"

  export default {
    data() {
      return {
        mobile:null
      }
    },
    methods: {
      regist() {
        this.mobile=this.$route.query.mobile;
        localStorage.setItem('Mobile',this.mobile);
        this.$router.push("/login/regist");
        this.$store.dispatch("setStep",1);
      }
    },
    components: { topBack }
  }
</script>