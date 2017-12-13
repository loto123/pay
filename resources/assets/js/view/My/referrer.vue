<template>
  <div id="referrer">
    <topBack title="推荐人信息" style="color:#fff"></topBack>
    <div class="flex flex-align-center flex-justify-center" style="height:100%;">
      <div class="referrer-container">
        <div class="header flex">
          <div class="referrer-image">
            <img src="/images/personal.jpg">
          </div>
          <div class="referrer-info">
            <h2>牛大王</h2>
            <div class="tel">电话:<span>31231321321</span></div>
            <div class="wx-number">微信号:<span>31231321321</span></div>
          </div>
        </div>
        <div class="code">
          <div class="code-image">
            <img src="/images/code.png">
          </div>
          <div class="add-referrer">用微信识别二维码添加您的推荐人</div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import topBack from "../../components/topBack";

export default {
  components: { topBack }
};
</script>

<style lang="scss" scoped>
#referrer {
  background: #26a2ff;
  height: 100vh;
  padding-top:2em;
  box-sizing: border-box; 
}
.referrer-container {
  background: #fff;
  width: 92%;
  border-radius: 0.7em;
}

.header {
  height: 4.5em;
  padding: 1em;
  .referrer-image {
    width: 4.5em;
    height: 4.5em;
    > img {
      display: block;
      width: 100%;
      border-radius: 50%;
    }
  }
  .referrer-info {
    margin-left: 1em;
    .tel,
    h2 {
      margin-bottom: 0.8em;
    }
    .wx-number,
    .tel {
      color: #999;
      font-size: 0.9em;
    }
    h2 {
      font-size: 1em;
      margin-top: 0.1em;
    }
  }
}
.code {
  padding-bottom: 2.5em;
  .code-image {
    max-width: 150px;
    margin: 2em auto;
    background: #f1f1f1;
    padding: 10px;
    border-radius: 5px;
    img {
      display: block;
      width: 100%;
    }
  }
  .add-referrer {
    text-align: center;
    font-size: 0.8em;
    color: #666;
  }
}
</style>
