<template>
    <div id="we-chat">
        微信登录中...
    </div>
</template>

<style lang="scss" scoped>

</style>

<script>

import request from '../../utils/userRequest'
import utils from '../../utils/utils'
import {Toast} from 'mint-ui'

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
        this.code = utils.getQueryString("code");
        this.state = utils.getQueryString("state");
        
        var _data = {
            code :this.code,
            state:this.state
        };
        request.getInstance().postData("api/auth/login/wechat",_data).then(res=>{

            if(!res.data.data.token){
                window.location.href = "/#/login/regist/"+"?oauth_user="+res.data.data.oauth_user;
            }else {
                Toast("微信登录成功");
                request.getInstance().setToken(res.data.data.token);
                
                setTimeout(()=>{
                    window.location.href = "/#/index";
                },2000);
            }
            
        }).catch();
    }
  }
  
}
</script>

