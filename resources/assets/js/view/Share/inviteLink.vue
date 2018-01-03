<template>
  <div id="share">
    <topBack title="分享" style="background:#38C3EC;color:#fff;"></topBack>
    <div class="back-img">
      <a href="javascript:;" class="share-btn" id="shareBtn" @click="shareBtn">分享</a>
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
  .back-img{
    background:url(/images/shareInvite.jpg) no-repeat;
    position: relative;
    width: 100%;
    height: 100%;
    background-size: 100% 100%;
  }
  .share-btn{
    background:#FF7B54;
    color: #fff;
    width: 10em;
    height:3em;
    line-height: 3em;
    position: absolute;
    left: 0;
    right:0;
    bottom:4em;
    margin:0 auto;
    text-align: center;
    border-radius: 5px;
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
    },

    data() {
      return {
        
      }
    },
    methods: {
      init(){
        var data={
          share_url :window.location.href.split('#')[0],
          list :['onMenuShareTimeline','onMenuShareAppMessage']
        }
        request.getInstance().getData("api/proxy/share",data)
					.then((res) => {
            console.log(res);
					})
					.catch((err) => {
						console.error(err);
					})
      },
      shareBtn(){
       
      }
    },
    components: { topBack},
  }
</script>