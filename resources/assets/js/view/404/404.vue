<template>
    <div id="FOF-page" class="flex flex-v flex-align-center">
        <div class="img-wrap">
            <div>
              <img src="/images/404.jpg" alt="" v-if="!reason">
            </div>
            <h3 >页面未找到</h3>
            <h3 v-if="reason" style="margin-top:0.4em;">原因：{{reason}}</h3>
        </div>
        <div class="index-button">
            <mt-button type="primary" size="large" @click="goIndex">回到首页</mt-button>
        </div>
    </div>
</template>

<style lang="scss" scoped>
#FOF-page{
    width: 100%;
    padding-top:8em;
    height: 100vh;
    background: #f5fdff;
    box-sizing: border-box;

    .img-wrap{
        width: 90%;

        >div{
          min-height: 5em;
          >img{
            width: 100%;
          }
        }

        h3{
            text-align: center;
            font-size:1.5em;
            color:#555;
        }
    }

    .index-button{
        margin-top:1em;
        width: 95%;
    }
}
</style>

<script>
export default {
    data(){
      return {
        reason: null,
      }
    },

    created(){
      this.init();
    },

    methods:{

      init(){
        if(this.$route.query.codeMsg){
          this.reason = this.$route.query.codeMsg;
        }
      },

      goIndex(){
          this.$router.push('/index');
      }
  }
}
</script>

