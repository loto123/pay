<template>
  <div id="Inform">
      <div id="top">
          <topBack title="消息中心">
              <div class="clear-inform flex flex-reverse flex-align-center">
                清空消息
              </div>
          </topBack>
      </div>
      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="flex flex-align-center flex-justify-center active">分润通知</div>
        <div class="flex flex-align-center flex-justify-center" @click="goUser">用户注册</div>
        <div class="flex flex-align-center flex-justify-center" @click="goSystemInfo">系统通知</div>
      </div>
      <div class="shareBenefit-box">
        <ul>
          <li class="flex flex-align-center flex-justify-between">
              <div class="left-content">
                <div class="personal-info flex">
                  <div class="flex-1 flex"><div class="personal-img">从<img src="/images/avatar.jpg"><span>21321321</span>获得</div></div>
                </div>
                <div class="date">2017-11-23  19:44:31</div>
              </div>
              <div class="shareBenefit-money active">+10</div>
          </li>
        </ul>
      </div>
  </div>
</template>


<script>
import topBack from "../../components/topBack.vue";
export default {
  data() {
    return {};
  },
  components: { topBack },
  methods: {
    goUser(){
      this.$router.push("/userRegister")
    },
    goSystemInfo(){
      this.$router.push('/systemInfo')
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
#top {
  width: 100%;
  background: #26a2ff;
  color: #fff;
  padding-top: 2em;
  box-sizing: border-box;
  .clear-inform{
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    padding-right: 0.8em;
  }
}

.tab-menu {
  width: 100%;
  height: 3em;

  > div {
    width: 50%;
    height: 100%;
    box-sizing: border-box;
  }
  .active {
    border-bottom: 0.2em solid #26a2ff;
    color: #26a2ff;
  }
}

.shareBenefit-box{
  margin-top: 1em;
  li{
    border: 1px solid #ddd;
    // line-height:2em;
    padding:0.5em 1em;
    .left-content{
      .date{
        color:#999;
        font-size: 0.8em;
      }
    }
    .shareBenefit-money{
      font-size: 1.4em;
      font-weight: normal;
    }
    .active{
      color:green;
    }
  }
}
.personal-info{
  .personal-img{
    margin-bottom:0.5em;
    img{
      display: inline-block;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      vertical-align: middle;
      margin:0 0.5em;
    }
    span{
      display: inline-block;
      margin-right:0.7em;
    }
  }
}
</style>



