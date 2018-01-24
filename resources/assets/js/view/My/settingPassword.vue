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
      valideTimes:false,

      status:null,    // 状态
      mobile:null,    // 手机号
      code:null, // 验证码
      password:null // 新密码
    };
  },

  created(){
    this.status = this.$route.query.status;
    this.mobile = this.$route.query.mobile;
    this.code = this.$route.query.code;
    console.log(this.status);
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

            // 两次的密码相同
            if(this.firstPassword == this.secondPassword){
                Loading.getInstance().open();
               
                // 重置密码    
                if(this.status == 'resetPassword'){

                    var _data = {
                        mobile :this.mobile,
                        code:this.code,
                        pay_password:this.secondPassword,
                    }

                    request.getInstance().postData('api/my/resetPayPassword',_data).then(res=>{
                        Loading.getInstance().close();
                        Toast("支付密码设置成功，正在跳转...");
                        
                        setTimeout(()=>{
                            this.$router.push('/index');
                        },1500);
                    }).catch();

                }else if(!this.status){
                    var _data = {
                        pay_password :this.firstPassword
                    };

                    request.getInstance().postData("api/my/setPayPassword",_data).then(res=>{
                        Loading.getInstance().close();
                        Toast("支付密码设置成功，正在跳转...");
                        
                        setTimeout(()=>{
                            this.backToLastPage();
                        },1500);
                        
                    }).catch(err=>{
                        Toast(err.data.msg);
                    });
                }

                // TODO:修改支付密码
              
            }else {
                Toast("两次密码输入不一致,请重新输入");
                this.valideTimes = false;
            }


        }
    },

    backToLastPage(){
        this.$router.go(-1);
    }
  }
};
</script>


