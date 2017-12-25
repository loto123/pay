<template>
    <div id="we-chat">
        微信登录中...
    </div>
</template>

<style lang="scss" scoped>

</style>

<script>

import request from '../../utils/userRequest'

export default {
    data(){
        return {
            state:null,
            code:null
        }
    },
  created(){
      this.init();
  },
  methods:{
    init(){
        this.code = this.$route.query.code;
        this.state = this.$route.query.state;
        console.log(this.code);
        console.log(this.state);
        var _data = {
            code :this.code,
            state:this.state
        };
        request.getInstance().postData("api/auth/login/wechat",_data).then(res=>{

        }).catch();

    }
  }
  
}
</script>

