<template>
  <div id="my">
    <section class="header-container">
      <div class="header">
        <div class="imggWrap">
            <img src="/images/transaction.png">
        </div>
        <h3>发起交易</h3>
        <div class="acc-number">账号:<span>18674231689</span></div>
      </div>
    </section>
    <section>
      <ul>
        <li>
          <mt-cell title="标题文字" to="//github.com" is-link value="带链接">
            <img slot="icon" src="/images/transaction.png" width="24" height="24">
          </mt-cell>
        </li>
        <li>
          <mt-cell title="标题文字" to="//github.com" is-link value="带链接">
            <img slot="icon" src="/images/transaction.png" width="24" height="24">
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

</style>


<script>
</script>