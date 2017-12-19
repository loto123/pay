<template>
  <div id="Inform">
      <div id="top">
          <topBack title="消息中心" :backUrl="'\/index\/'">
              <div class="clear-inform flex flex-reverse flex-align-center">
                清空消息
              </div>
          </topBack>
      </div>
      <div class="tab-menu flex flex-align-center flex-justify-center" >
        <div class="flex flex-align-center flex-justify-center" @click="goShareBenefit">分润通知</div>
        <div class="flex flex-align-center flex-justify-center" @click="goUser">用户注册</div>
        <div class="flex flex-align-center flex-justify-center active" @click="goSystemInfo">系统通知</div>
      </div>
      <div class="systemInfo-box">
        <ul>
          <li @click="goDetails">
              <div class="info-header flex flex-align-end  flex-justify-between">
                <div class="title">系统消息</div>
                <div class="date">2017-11-23 19:44:31</div>
              </div>
              <div class="content">321321</div>
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
    goShareBenefit() {
      this.$router.push("/inform");
    },
    goUser() {
      this.$router.push("/userRegister");
    },
    goSystemInfo() {
      this.$router.push("/systemInfo");
    },
    goDetails(){
      this.$router.push("/systemInfo/system_Details");
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
  .clear-inform {
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

.systemInfo-box{
  margin-top: 1em;
  li {
    border: 1px solid #ddd;
    padding: 0.5em 1em;
    .info-header{
      margin-bottom:0.7em;
      .date{
        color:#999;
        font-size: 0.8em;
      }
      .title{
        color:#333;
        font-size: 1em;
      }
    }
    .content{
      color:#999;
      font-size: 0.8em;
      width: 100%;
    }
  }
}

</style>



