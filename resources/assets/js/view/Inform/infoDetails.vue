<template>
  <div id="Inform">
      <div id="top">
          <topBack title="消息详情"></topBack>
      </div>
      <div class="details-content">
        <h2>系统消息</h2>
        <div class="details-date">2017-11-23 19:44:31</div>
        <div class="content">
          的房间思考对方即可赶紧放开打开了房贷款开关对两个
          客服附近的司法几点开始开个房都看过了富可敌国理发
          店开关大概开关佛牌的客观佛跑得快。
        </div>
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
}
.details-content{
  padding:1em;
  h2{
    color:#333;
    font-size: 1.1em;
    margin-bottom:0.5em;
  }
  .details-date{
    color:#999;
    font-size: 0.9em;
    margin-bottom:0.8em;
  }
  .content{
    color:#999;
    font-size: 1em;
    width:100%;
    text-indent: 2em;
    line-height: 1.7em;
  }
}
</style>



