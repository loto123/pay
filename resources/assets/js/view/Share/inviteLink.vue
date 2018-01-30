<template>
  <div id="share">
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
  import request from "../../utils/userRequest"
  import Loading from "../../utils/loading"
  import moment from 'moment'
  import { Toast,MessageBox  } from 'mint-ui'
  import wx from 'weixin-js-sdk'
  
  export default {
    created() {
      this.init().then(res=>{
        if (res) {
          this.shareContent()
        }
      });
    },

    data() {
      return {
        mobile:null
      }
    },
    methods: {
      init() {
        var data = {
          share_url: window.location.href.split('#')[0],
          list: ['onMenuShareTimeline', 'onMenuShareAppMessage']
        }
        return Promise.all([request.getInstance().getData("api/my/info"),request.getInstance().getData("api/proxy/share", data)])
          .then((res) => {
            this.mobile=res[0].data.data.mobile;
            var Data = res[1].data.data;
            var content=JSON.parse(Data.config);
            wx.config(content);
            return Promise.resolve(true);
          })
          .catch((err) => {
            Toast(err.data.msg);
          })
      },
      shareContent() {
        let url=window.location.href.split('#')[0];
        let links = url+'/#/shareUser/inviteLink/download?mobile='+this.mobile;
        let title = '聚宝朋';
        let desc = '广聚天下朋友，共享时代财富';
        let imgUrl = '';
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
      },
      shareBtn(){
        MessageBox({
          title: '提示',
          message: '请点击微信右上角进行分享',
          showCancelButton: false
        });
      }
    }
  }
</script>