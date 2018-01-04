<template>
  <div id="share">
    <topBack title="分享" style="background:#38C3EC;color:#fff;"></topBack>
    <div class="back-img">
      <a href="javascript:;" class="share-btn" id="shareBtn" @click="shareBtn">分享</a>
      <div class="text">
        分享到朋友圈或社交网络，可以获得更多的用户免费注册分享越多，收益越多
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

  .back-img {
    background: url(/images/shareInvite.jpg) no-repeat;
    position: relative;
    width: 100%;
    height: 100%;
    background-size: 100% 100%;
  }

  .share-btn {
    background: #FF7B54;
    color: #fff;
    width: 10em;
    height: 3em;
    line-height: 3em;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 4.5em;
    margin: 0 auto;
    text-align: center;
    border-radius: 5px;
  }

  .text {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 1em;
    color: #fff;
    width: 94%;
    margin: auto;
    line-height: 1.2em;
  }
</style>

<script>
  import topBack from "../../components/topBack";
  import request from "../../utils/userRequest"
  import Loading from "../../utils/loading"
  import moment from 'moment'
  import { Toast } from 'mint-ui'
  import wx from 'weixin-js-sdk'
  export default {
    created() {
      this.init();
      this.shareContent();
    },

    data() {
      return {

      }
    },
    methods: {
      init() {
        var data = {
          share_url: window.location.href.split('#')[0],
          list: ['onMenuShareTimeline', 'onMenuShareAppMessage']
        }
        request.getInstance().getData("api/proxy/share", data)
          .then((res) => {
            var Data = res.data.data;
            wx.config({
              debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
              appId: Data.appId,   // 必填，公众号的唯一标识
              timestamp: Data.timestamp, // 必填，生成签名的时间戳
              nonceStr: Data.nonceStr,   // 必填，生成签名的随机串
              signature: Data.signature, // 必填，签名，见附录1
              jsApiList: Data.jsApiList  // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });
          })
          .catch((err) => {
            console.error(err);
          })
      },
      shareContent() {
        wx.ready(() => {
          //分享给朋友
          wx.onMenuShareAppMessage({
            title: '', // 分享标题
            desc: '', // 分享描述
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '', // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
              // 用户确认分享后执行的回调函数
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          })
          //分享到朋友圈
          wx.onMenuShareTimeline({
            title: '', // 分享标题
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '', // 分享图标
            success: function () {
              // 用户确认分享后执行的回调函数
            },
            cancel: function () {
              // 用户取消分享后执行的回调函数
            }
          })
        })
      }
    },
    components: { topBack },
  }
</script>