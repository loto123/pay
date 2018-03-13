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
    right: 0;
    bottom: -115px;
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
  import wx from 'weixin-js-sdk'
  
  export default {
    created() {
      this.init().then(res=>{
        if (res) {
          this.initInfo();
        }
      });
    },

    data() {
      return {
        thumb: null,
        name: null,
        QRCode: null,
        mobile:null
      }
    },
    methods: {
      init() {
        var data = {
          share_url: window.location.href.split('#')[0],
          list: ['onMenuShareTimeline', 'onMenuShareAppMessage']
        }
        Loading.getInstance().open();
        return Promise.all([request.getInstance().getData("api/proxy/qrcode"), request.getInstance().getData("api/proxy/share", data)])
          .then(res => {
            this.QRCode = res[0].data.data.url;
            this.name = res[0].data.data.name;
            this.thumb = res[0].data.data.thumb;

            var Data = res[1].data.data;
            var content = JSON.parse(Data.config);
            wx.config(content);
            return Promise.resolve(true);
            Loading.getInstance().close();
          }).catch(err => {
            Toast(err.data.msg);
          });
      },
      initInfo(){
        Loading.getInstance().open();
        request.getInstance().getData("api/my/info").then(res=>{
            this.mobile=res.data.data.mobile;
            this.shareContent();
            Loading.getInstance().close();
          })
          .catch((err) => {
            Toast(err.data.msg);
            Loading.getInstance().close();
          })
      },
      shareContent() {
        let url = window.location.href.split('#')[0];
        let links = url + '/#/shareUser/inviteLink/download?mobile=' + this.mobile;
        let title = '邀请您加入';
        let desc = '广聚天下朋友，共享时代财富';
        let imgUrl = url + '/images/logo.png';
        wx.ready(() => {
          //分享给朋友
          wx.onMenuShareAppMessage({
            title: title, // 分享标题
            desc: desc, // 分享描述
            link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: imgUrl, // 分享图标
            success: function () {
              // 用户确认分享后执行的回调函数
              Toast('成功分享给朋友');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
              Toast('分享失败，您取消了分享');
            }
          })
          //分享到朋友圈
          wx.onMenuShareTimeline({
            title: title, // 分享标题
            link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: imgUrl, // 分享图标
            success: function () {
              // 用户确认分享后执行的回调函数
              Toast('成功分享到朋友圈');
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
              Toast('分享失败，您取消了分享');
            }
          })
        })
      }
    },
    components: { topBack }
  }
</script>