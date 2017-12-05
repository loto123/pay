<template>
  <div id="my">
    <section class="header-container">
      <div class="header">
        <div class="imggWrap">
            <img src="/images/logo.png">
        </div>
        <h3>发起交易</h3>
        <div class="acc-number">账号:<span>18674231689</span></div>
      </div>
    </section>
    <section>
      <ul class="my-list">
        <li>
          <mt-cell title="推荐人" is-link to="">
            <img slot="icon" src="/images/logo.png" width="24" height="24">
            <span>icon 是图片</span>
          </mt-cell>
        </li>
        <li>
          <mt-cell title="银行卡管理" is-link to="">
            <img slot="icon" src="/images/logo.png" width="24" height="24">
            <span>icon 是图片</span>
          </mt-cell>
        </li>
        <li>
          <mt-cell title="实名认证" is-link to="">
            <img slot="icon" src="/images/logo.png" width="24" height="24">
            <span>icon 是图片</span>
          </mt-cell>
        </li>
        <li>
          <mt-cell title="查看那结算卡" is-link to="">
            <img slot="icon" src="/images/logo.png" width="24" height="24">
            <span>icon 是图片</span>
          </mt-cell>
        </li>
        <li>
          <mt-cell title="更多设置" is-link to="/#/my/set">
            <img slot="icon" src="/images/logo.png" width="24" height="24">
            <span>icon 是图片</span>
          </mt-cell>
        </li>
      </ul>
    </section>
  </div>
</template>

<style lang="scss" scoped>
.header-container {
  height: 12em;
  padding-top: 4.5em;
  box-sizing: border-box;
  // border-bottom: 1px solid #ccc;
}
.header {
  text-align: center;
  .imggWrap {
    width: 100%;
    background: #fff;
    > img {
      width: 3.5em;
      height: 3.5em;
      display: block;
      margin: auto;
      border-radius: 50%;
    }
  }

  h3,.acc-number{
    font-size: 1em;
    text-align: center;
    margin-top: 0.5em;
    color:#616161;
  }
  .acc-number {
    color:#333;
    span{
      color:#616161;
    }
  }
}
.my-list{
  border-bottom: 1px solid #d9d9d9;
  li{
    .mint-cell{
      background-image: none;
      background-size: 100% 1px;
      background-repeat: no-repeat;
      background-position: top;
    }
  }
}

</style>


<script>
</script>