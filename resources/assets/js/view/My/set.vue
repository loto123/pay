<template>
  <div id="set">
    <topBack title="更多设置"></topBack>
    <section>
      <ul class="list">
        <li>
          <mt-cell title="修改登录密码" is-link to=""></mt-cell>
        </li>
        <li>
          <mt-cell title="修改支付密码" is-link to=""></mt-cell>
        </li>
      </ul>
    </section>
  </div>
</template>


<script>
 import topBack from '../../components/topBack'

 export default{
  components: { topBack }
 }
</script>

<style lang="scss" scoped>
  .list{
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
