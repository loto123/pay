<template>
  <div id="realAuth">
    <topBack title="实名认证"></topBack>
    <div class="realAuth-container">
      <div class="realAuth-box">
        <mt-field label="姓名" placeholder="请填写本人真实姓名" type="text" v-model="name"></mt-field>
        <mt-field label="身份证号" placeholder="请填写本人身份证号" type="text" v-model="id_number"></mt-field>
        <section class="account-container">
          <div class="account-box flex flex-align-center">
            <span>账号:</span>
            <em class="flex-1 number">{{mobile}}</em>
          </div>
        </section>
        <section class="input-wrap-box">
          <div class="input-wrap flex flex-align-center">
            <span>验证码:</span>
            <input type="text" placeholder="请输入验证码" class="flex-1" v-model="code">
            <mt-button type="default" class="flex-1" @click="sendYZM">发送验证码{{computedTime?"("+computedTime+")":""}}</mt-button>
          </div>
        </section>
      </div>
      <div class="submit-button flex flex-justify-center">
        <mt-button type="primary" size="large" @click="realAuth">确定</mt-button>
      </div>
    </div>
  </div>
</template>


<script>
  import topBack from "../../components/topBack";
  import request from '../../utils/userRequest';
  import { Toast } from "mint-ui";
  export default {
    data () {
      return {
        name :null,
        id_number :null,
        code:null,
        mobile:null,

        computedTime:null  //倒数计时
      }
    },
    created(){
      this.getMobile();
    },
    components: { topBack },
    methods: {
      //实名认证
      realAuth() {
        var _this=this;
        var _data={
          name :this.name,
          id_number :this.id_number,
          code:this.code
        }

        if(!this.name){
          Toast("请填写本人真实姓名");
					return 
        }else if(!this.id_number){
          Toast("请填写本人身份证号");
					return 
        }else if(!this.code){
          Toast("请输入验证码");
					return 
        }

        request.getInstance().postData('api/my/identify',_data).then((res) => {
          Toast('认证成功');
          this.$router.push('/my'); //认证成功，回到我的页面
        }).catch((err) => {
          Toast({
            message: err.data.msg,
            duration: 800
          });
        })
      },
      getMobile(){
        this.mobile=this.$route.query.mobile;
      },
      //短信验证码
      sendYZM() {
				if(this.computedTime !=null){
					return;
				}
				var _data = {};
				_data.mobile = this.$route.query.mobile;

				this.computedTime = 60;
				var timer = setInterval(() => {
					this.computedTime--;
					if (this.computedTime == 0) {
						this.computedTime=null;
						clearInterval(timer);
					}
				}, 1000)
				
				Loading.getInstance().open();
				request.getInstance().postData("api/auth/sms", _data).then((res) => {
					Loading.getInstance().close();
				}).catch((err) => {
					Toast(err.data.msg);
					Loading.getInstance().close();
				})
			}
    }
  };
</script>

<style lang="scss" scoped>
  #realAuth {
    background: #eee;
    height: 100vh;
    padding-top: 2em;
    box-sizing: border-box;
  }

  .realAuth-box {
    border-bottom: 1px solid #d9d9d9;
  }

  .account-container {
    background: #fff;
    padding-left: 10px;
    .account-box {
      width: 100%;
      height: 3em;
      border-top: 1px solid #d9d9d9;
      span {
        display: inline-block;
        width: 105px;
      }
      .number {
        color: #666;
        font-size: inherit;
      }
    }
  }

  .input-wrap-box {
    background: #fff;
    padding-left: 10px;
  }

  .input-wrap {
    width: 100%;
    height: 3em;
    border-top: 1px solid #D9D9D9;
    span {
      display: inline-block;
      width: 105px;
    }
    .mint-button {
      font-size: 0.9em;
    }
    .mint-button--default {
      background: #fff;
    }
    input {
      border: none;
      outline: none;
      text-rendering: auto;
      color: initial;
      letter-spacing: normal;
      word-spacing: normal;
      text-transform: none;
      text-indent: 0px;
      text-shadow: none;
      display: inline-block;
      text-align: start;
      height: 2em;
      box-sizing: border-box;
      width: 20%;
      font-size: inherit;
    }
  }

  .submit-button {
    width: 90%;
    margin: auto;
    margin-top: 3em;
  }
</style>