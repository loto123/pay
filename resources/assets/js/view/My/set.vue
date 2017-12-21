<template>
  <div id="set">
    <topBack title="更多"></topBack>
    <section>
      <ul class="list">
        <li>
          <mt-cell title="修改登录密码" is-link to="/my/login_password"></mt-cell>
        </li>
        <li @click="verfyCode">
          <mt-cell title="修改支付密码" is-link></mt-cell>
        </li>
      </ul>
      <ul class="list mt1">
        <li>
          <mt-cell title="帮助与反馈" is-link to=""></mt-cell>
        </li>
        <li>
          <mt-cell title="关于" is-link to="/my/set/about"></mt-cell>
        </li>
      </ul>
    </section>
  </div>
</template>


<script>
 import topBack from '../../components/topBack'

 export default{
  data () {
    return {
      mobile:null
    }
  },
  components: { topBack },
  methods: {
    verfyCode(){
      this.mobile=this.$route.query.mobile;
      this.$router.push('/my/verfy_code?'+'mobile='+this.mobile);
    }
  }
 }
</script>

<style lang="scss" scoped>
  .mt1{
    margin-top:1em;
  }
  #set{
    background: #eee;
    padding-top: 2em;
    box-sizing: border-box;
    height: 100vh;
  }
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
