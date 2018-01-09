<template>
  <div id="setting-password">
      <password 
        :setSwitch = "passwordSwitch" 
        :settingPasswordSwitch = "true"
        :secondValid = "valideTimes"  
        v-on:callBack ="getPassword"
        v-on:hidePassword="backToLastPage"
      >
      </password>
  </div>
</template>

<style lang="scss" scoped>

</style>

<script>
import request from "../../utils/userRequest";
import Loading from "../../utils/loading";
import password from "../../components/password";
import {Toast} from "mint-ui"

export default {
  data() {
    return {
      passwordSwitch: true,
      firstPassword:null,
      secondPassword:null,
      valideTimes:false
    };
  },
  components: { password },
  methods: {
    getPassword(value) {
        console.log(value);

        if(this.valideTimes == false){
            this.firstPassword = value;
            this.valideTimes = true;
        }else {
            this.secondPassword = value;
            if(this.firstPassword == this.secondPassword){
                Loading.getInstance().open();
                var _data = {
                    pay_password :this.firstPassword
                };
                request.getInstance().postData("api/my/setPayPassword",_data).then(res=>{
                    console.log(res);
                    Toast("支付密码设置成功，正在跳转...");
                    Loading.getInstance().close();
                    
                    setTimeout(()=>{
                        this.backToLastPage();
                    },1500);
                    
                }).catch(err=>{
                    Toast(err.data.msg);
                });
            }
        }
    },
    backToLastPage(){
        this.$router.go(-1);
    }
  }
};
</script>


