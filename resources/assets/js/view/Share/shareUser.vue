<template>
  <div id="share">
    <topBack title="展业" style="background:#38C3EC;color:#fff;"></topBack>
    <ul class="share-user">
      <li class="flex flex-justify-around" @click="goGeneralize">
        <div>图标</div>
        <div>二维码图片链接</div>
        <div class="iconfont">&#xe62e;</div>
      </li>
      <li class="flex flex-justify-around" @click="goInviteLink">
          <div>图标</div>
          <div>分享注册邀请链接</div>
          <div class="iconfont">&#xe62e;</div>
        </li>
    </ul>
  </div>
</template>

<style lang="scss" scoped>
  #share {
    box-sizing: border-box;
    padding-top: 2em;
    height: 100vh;
    width: 100%;
    background: #eee;
  }

  .share-user {
    padding: 1em 0.5em;
    li {
      height: 5em;
      line-height:5em;
      background: #fff;
      margin-bottom:1em;
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
      goGeneralize(){
        this.$router.push('/shareUser/generalize')
      },
      goInviteLink(){
        this.$router.push('/shareUser/inviteLink')
      }
    },
    components: { topBack},
  }
</script>