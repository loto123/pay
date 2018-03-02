<template>
  <div id="share">
    <topBack title="二维码推广" style="background:#38C3EC;color:#fff;"></topBack>
    <div class="reffer-box">
      <div class="header">
        <div class="imgWrap">
          <img :src="thumb">
        </div>
        <h3>推荐人: {{name}}</h3>
      </div>
      <div class="code-box">
        <div class="code">
            <img :src="QRCode">
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
  }

  .header {
    padding-top: 7em;
    .imgWrap {
      img {
        width: 4.5em;
        height: 4.5em;
        border-radius: 50%;
      }

    }
    h3 {
      font-size: 1em;
      margin-top: 0.5em;
      color: #fff;
    }
  }

  .reffer-box {
    width: 100%;
    margin: 0 auto;
    height: 21em;
    text-align: center;
    background: #38C3EC;
    position: relative;
  }

  .code-box {
    position: absolute;
    left: 0;
    right:0;
    bottom:-115px;
    margin: auto;
    .code {
      margin-bottom: 0.2em;
    }
  }
</style>

<script>
  import topBack from "../../components/topBack";
  import request from "../../utils/userRequest"
  import Loading from "../../utils/loading"
  import { Toast } from 'mint-ui'

  export default {
    created() {
      this.init();
    },

    data() {
      return {
        thumb:null,
        name:null,
        QRCode:null
      }
    },
    methods: {
      init() {
        Loading.getInstance().open();
        request.getInstance().getData("api/proxy/qrcode").then(res => {
          this.QRCode=res.data.data.url;
          this.name=res.data.data.name;
          this.thumb=res.data.data.thumb;
          Loading.getInstance().close();
        }).catch(err => {
          Toast(err.data.msg);
        });
      }
    },
    components: { topBack }
  }
</script>