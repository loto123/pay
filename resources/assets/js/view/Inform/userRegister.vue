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
      <div class="flex flex-align-center flex-justify-center" @click="goshareBenefit">分润通知</div>
      <div class="flex flex-align-center flex-justify-center active">用户注册</div>
      <div class="flex flex-align-center flex-justify-center" @click="goSystemInfo">系统通知</div>
    </div>
    <div class="userRegister-box">
      <ul>
        <li class="flex flex-align-center flex-justify-between">
          <div class="left-content">
            <div class="title flex">
              <div class="flex-1 flex">新用户注册通知</div>
            </div>
            <div class="date">2017-11-23  19:44:31</div>
          </div>
          <div class="status">您推荐的<span>18676456235</span>成功注册</div>
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
    goshareBenefit() {
      this.$router.push("/inform");
    },
    goUser() {
      this.$router.push("/userRegister");
    },
    goSystemInfo() {
      this.$router.push("/systemInfo");
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

.userRegister-box {
  margin-top: 1em;
  li {
    border: 1px solid #ddd;
    padding: 0.5em 1em;
    .left-content {
      .title {
        margin-bottom: 0.7em;
      }
      .date {
        color: #999;
        font-size: 0.8em;
      }
    }
    .status {
      color: #999;
      font-size: 0.8em;
    }
  }
}
</style>



