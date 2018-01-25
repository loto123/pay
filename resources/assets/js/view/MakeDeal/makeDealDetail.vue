<template>
    <div id="deal-detail">
        <topBack style="background:#eee;">
          <div 
            style="width:100%;
            padding-right:1em;
            box-sizing:border-box;" 
            class="flex flex-reverse"

            v-if="recordList.length==0 && isManager"
            @click="cancelTrade"
            >
              撤销任务
          </div>
        </topBack>

        <section class="big-winner-tip flex flex-v flex-align-center flex-justify-center" @click="goTipPage" v-if="allow_reward">
            <p>任务</p>
            <p>加速</p>
        </section>

        <section class="mission-status " v-bind:class="[status!=3?'active':'disable']">
          <p>
            {{status!=3?'任务进行中':"任务已关闭"}}
          </p>
        </section>
        
        <deal-content :renderData = "renderData"></deal-content>

        <section class="pay-wrap flex flex-v flex-align-center" v-if="status!=3">

            <div class="pay-money flex flex-align-center flex-justify-around">
                <label for="">交钻</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数" v-model="moneyData.payMoney">
                </div>
            </div>

            <div class="get-money flex flex-align-center flex-justify-around">
                <label for="">拿钻</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数" v-model="moneyData.getMoney">
                </div>
            </div>

            <mt-button type="primary" size="large" @click="callPassword">确认</mt-button>
        </section>
        
        <!-- 参与玩家记录 -->
        <section class="pay-record ">
            <div class="title flex flex-v">
              <div class="top flex flex-align-center">
                <span>参与人</span>
              </div>

              <div class="bottom flex flex-align-center flex-justify-between">
                <img :src="item.user.avatar?item.user.avatar:'/images/avatar.jpg'" 
                  alt="" 
                  v-for="item in joiner" 
                >
                
                <span class="info-friend" @click="showMemberChoise" v-if="status!=3">提醒好友</span>
              </div>
            </div>
            
            <ul class="flex flex-v flex-align-center" v-if="isShow">

                <li v-for=" item in recordList">
                    <slider @deleteIt="deleteIt(item.id)" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="item.stat==2 && item.allow_cancel?false:true">
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <div class="img-wrap flex-2">
                                <img :src=item.user.avatar alt="">
                            </div>
                            <span class="flex-8">{{item.user.name}}</span>
                            <div class="pay-money-text flex flex-v flex-justify-between flex-align-center flex-4">
                                <span class="money" v-bind:class="[item.stat == 1?'':'green-color']">{{item.stat==2?'+':''}}{{item.amount}}</span>
                                <span class="title" v-if="item.stat!=3"> {{item.stat==1?"付钻":"拿钻"}}</span>
                                <span class="title" v-if="item.stat==3"> 已撤回</span>
                                <!-- <span class="title"> {{item.stat==1?"放钱":"拿钱"}}</span> -->
                            </div>
                        </div>
                    </slider> 
                </li>
                
            </ul>
        </section>

        <section id="qrcode" class="flex flex-justify-center"></section>
        <h3 class="notice">扫描二维码快速加入任务</h3>

        <passwordPanel 
          :setSwitch="passWordSwitch" 
          :settingPasswordSwitch="false" 
          :secondValid="false" 
          v-on:hidePassword="hidePassword" 
          v-on:callBack ="submitData">
        </passwordPanel>

        <choiseMember 
          :isShow = "choiseMemberSwitch"
          v-on:hide = "hideMemberChoise"
          :dataList = "memberList"
          v-on:submit ="addMembersNotice"
        >
        </choiseMember>  
    </div>
</template>

<style lang="scss" scoped>
.green-color {
  color: green;
}

#deal-detail {
  background: #eee;
  min-height: 100vh;
  padding-top: 2em;
}

.big-winner-tip {
  width: 4em;
  height: 4em;
  border-radius: 50%;
  background: #26a2ff;
  position: absolute;
  right: 2em;

  p {
    text-align: center;
    font-size: 0.9em;
    color: #fff;
  }
}

.mission-status{
  width: 3em;
  height: 6em;
  position: absolute;
  left: 2em;

  p{
    width: 1em;
    display: block;
    margin:0 auto;
    margin-top:0.5em;
  }
}

.active{
  background: #26a2ff;
  p{
    color:#fff;
  }
}

.disable{
  background: #aaa;
  p{
    color:#fff;
  }
}

.pay-wrap {
  .pay-money {
    background: #fff;
    width: 90%;
    border-radius: 0.2em;
    height: 2.5em;

    label {
      width: 40%;
      padding-left: 0.5em;
      padding-right: 0.5em;
      box-sizing: border-box;
    }

    .input-wrap {
      height: 100%;
      width: 60%;
      input {
        box-sizing: border-box;
        font-size: 1em;
        padding-left: 0.5em;
        width: 100%;
        border: none;
        outline: none;
        height: 100%;
      }
    }
  }

  .get-money {
    @extend .pay-money;
    margin-top: 1em;
  }
}

.mint-button {
  margin-top: 1em;
  width: 90%;
}

.pay-record {
  padding-top: 0.5em;
  .title {
    width: 90%;
    height: 4.5em;
    line-height: 2em;
    background: #fff;
    margin: 0 auto;

    .top {
      height: 2em;
      width: 100%;
      padding-left: 0.5em;
      box-sizing: border-box;
      span {
        font-size: 1em;
        color: #555;
      }
    }

    .bottom {
      width: 100%;
      height: 3.5em;
      img {
        width: 2em;
        height: 2em;
        display: block;
        margin-left: 0.5em;
      }
    }

    .info-friend {
      margin-right: 0.5em;
      background: green;
      color: #fff;
      padding-left: 0.3em;
      padding-right: 0.3em;
      border-radius: 0.3em;
      font-size: 0.9em;
    }
  }

  ul {
    margin-top: 0.5em;
    li {
      margin-top: 0.2em;
      width: 90%;
      overflow-x: hidden;
      .slider-item {
        box-sizing: border-box;
        padding-left: 0.5em;
        padding-right: 0.5em;
        height: 3em;

        .pay-money-text {
          width: 20%;
          height: 100%;
          .money {
            font-size: 1.1em;
            width: 100%;
            line-height: 2em;
            height: 50%;
          }

          .title {
            font-size: 0.9em;
            height: 50%;
            width: 100%;
            background: #fff;
          }
        }
          .img-wrap{
              img {
                  width: 2.5em;
                  height: 2.5em;
                  display: block;
              }
          }


        span {
          display: block;
          text-align: center;
        }
      }
      /*#slider-component {
        margin-top: 0.5em;
      }*/
    }
  }
}

#qrcode {
  margin-top: 1.5em;
}

.notice {
  margin-top: 1em;
  padding-bottom: 1.5em;
  text-align: center;
  font-size: 0.9em;
  color: #999;
}


</style>


<script>
import topBack from "../../components/topBack";
import slider from "../../components/slider";
import dealContent from "./dealContent";
import passwordPanel from "../../components/password";
import request from "../../utils/userRequest";
import {Toast,MessageBox} from "mint-ui"
import choiseMember from "./choiseMember.vue"
import Loading from "../../utils/loading";

import qrCode from "../../utils/qrCode";

export default {
  name: "makeDealDetail",
  components: { topBack, slider, dealContent, passwordPanel ,choiseMember},

  data() {
    return {
      passWordSwitch: false,
      isShow:false,
      isManager:false,            // 是否是任务发起者
      renderData: {
        name: null,
        user:{
          avatar:null
        },
        avatar:null,
        
      },
      moneyData: {
        payMoney: null,
        getMoney: null
      },
      payType: null,              // 支付方式，取钱get 放钱put
      transfer_id:"",             // 任务id
      shop_id:"",
      password:"",                // 支付密码
      allow_reward:false,         // 是否允许打赏
      joiner:[],                  // 任务的参与者，需要提醒的人
      memberList:[],              //成员数组
      
      status:null,
      recordList:[],

      choiseMemberSwitch:false,
    };
  },
  created() {
    this.init();
  },
  mounted() {
    this._getQRCode();
  },
  methods: {
    // 撤销任务
    deleteIt(id) {
      Loading.getInstance().open();
      var _data={
        record_id :id
      };

      request.getInstance().postData("api/transfer/withdraw",_data).then(res=>{
        Loading.getInstance().close();
        Toast("撤销成功");
        
        setTimeout(()=>{
          this.init();
        },1500);
      }).catch(err=>{
        Toast("撤销失败");
        Loading.getInstance().close();
      });
      
    },

    init() {
      Loading.getInstance().open();
      this.transfer_id = this.$route.query.id;
      var _data = {
        transfer_id: this.transfer_id
      };

      request
        .getInstance()
        .getData("api/transfer/show" + "?transfer_id=" + this.transfer_id)
        .then(res => {
          this.joiner = res.data.data.joiner;
          this.renderData = res.data.data;
          this.recordList = res.data.data.record;
          this.shop_id = res.data.data.shop_id;
          this.allow_reward = res.data.data.allow_reward;
          this.isManager = res.data.data.allow_cancel;
          this.status = res.data.data.status;
          this.isShow = true;

          Loading.getInstance().close();
        })
        .catch(err => {
          console.error(err);
        });
    },

    goTipPage() {
      this.$router.push("/makeDeal/deal_tip" + "?id=" + this.transfer_id);
    },

    showPassword() {
      this.passWordSwitch = true;
    },
    hidePassword() {
      this.passWordSwitch = false;
    },

    callPassword(){

      if(this.payType == "put"){
        var _put = this.moneyData.payMoney;

        if((parseFloat(_put)).toString().indexOf(".") != -1 || isNaN(Number(_put))){
          this.moneyData.payMoney = null;

          Toast("分数只能是整数");
          Loading.getInstance().close();
          return;

        }else{

          Loading.getInstance().open();

          request.getInstance().getData("api/my/info").then(res=>{

            var _passwordStatus = res.data.data.has_pay_password;

            if(!_passwordStatus){

              Loading.getInstance().close();
              Toast("您还未设置支付密码，即将跳转设置页面");
              setTimeout(() => {
                this.$router.push("/my/setting_password");
              }, 2000);

            }else {

              Loading.getInstance().close();
              this.showPassword();

            }

          }).catch(err=>{

            Loading.getInstance().close();

          });
        }

      }else if(this.payType == "get"){
        this.submitData();
      }else {
        Toast("请填写拿钱数额或取钱数额");
      }
    },

    addMembersNotice(dataList){
      if(!dataList){
        return;
      }

      Loading.getInstance().open();

      var _tempList = [];
      for(let i = 0; i<dataList.length; i++){
        _tempList.push(dataList[i].id);
      }

      var _data ={
        transfer_id:this.transfer_id,
        friend_id:_tempList
      };  

      request.getInstance().postData("api/transfer/notice",_data).then(res=>{
        Loading.getInstance().close();   
        Toast("编辑提醒成员成功...");
        setTimeout(()=>{
          this.init();
        },2000);
        
      }).catch(err=>{
        Loading.getInstance().close();
        Toast(err.data.msg);
      });

    },

    // 提交任务  拿钱或者付钱
    submitData(password){
      // 放钱
      if(this.payType == "put"){

        var _data = {
          transfer_id :this.transfer_id,
          points :this.moneyData.payMoney,
          action :"put",
          pay_password:password
        }

        request.getInstance().postData("api/transfer/trade",_data).then(res=>{
          Loading.getInstance().close();
          Toast("放钻成功");
          this.moneyData.payMoney = null;
          setTimeout(()=>{
            this.init();
          },1500);
        }).catch(err=>{
          Loading.getInstance().close();

          Toast(err.data.msg);
        });

        this.hidePassword();

      }else if(this.payType == "get"){
        // 拿钱
        var _data = {
          transfer_id :this.transfer_id,
          points :this.moneyData.getMoney,
          // action :"realGet",
        }

        request.getInstance().postData("api/transfer/realget",_data)
          .then(res=>{
            var _data = {
              amount:res.data.data.amount,
              real_amount:res.data.data.real_amount
            }

            return Promise.resolve(_data);
          })
          .then(realData=>{
            MessageBox.confirm("实际拿钻"+ realData.real_amount+ ",手续费" + Math.floor((realData.amount- realData.real_amount)*100)/100 + "钻石").then(action => {

              var _data = {
                transfer_id :this.transfer_id,
                points :this.moneyData.getMoney,
                action :"get",
              }

              request.getInstance().postData("api/transfer/trade",_data)
                .then(res=>{
                  Loading.getInstance().close();
                  Toast("从公会中拿钱成功");
                  this.moneyData.getMoney = null;
                  setTimeout(()=>{
                    this.init();
                  },1500);

               }).catch(err=>{
                  Loading.getInstance().close();
                  Toast(err.data.msg);
                  setTimeout(()=>{
                      this.init();
                  },1000);
                  console.error(err);
              });

            }).catch(err=>{

            });
           })
          .catch(err=>{
            Toast(err.data.msg);
          });
        return;
      }
    },

    getResult(result) {
      this.password = result;
    },
    _getQRCode() {
      var qrcode = new QRCode(document.getElementById("qrcode"), {
        width: 100, //设置宽高
        height: 100
      });

      qrcode.makeCode(window.location.href);
    },
    cancelTrade(){
      var _data = {
        transfer_id:this.transfer_id
      }
      request.getInstance().postData('api/transfer/cancel',_data).then(res=>{
        Toast("撤销任务成功");
        setTimeout(()=>{
          this.$router.push("/makeDeal/my_deal");
        },1500);
      }).catch(err=>{
        Toast(err.data.data.msg);
      });
    },

    hideMemberChoise(){
      this.choiseMemberSwitch = false;
    },
    
    // 初始化提醒玩家列表
    initMemberList(res){
      this.memberList = [];

      for(let i = 0; i<res.data.data.members.length; i++){
          var _temp = {};
          _temp = res.data.data.members[i];
          _temp.checked = false;
          for(let j = 0; j<this.joiner.length; j++){
            if(this.joiner[j].user.id == _temp.id){
              _temp.checked = true;
              continue;
            }
          }
          this.memberList.push(_temp);

        }
    },
    // 获取所有要提醒的成员名单
    showMemberChoise(){
      
      Loading.getInstance().open();
      request.getInstance().getData('api/shop/members/'+this.shop_id).then(res=>{
        this.initMemberList(res);
        Loading.getInstance().close();

        if(res.data.data.members.length == 0){
          Toast("当前公会无成员");
          return;
        }

        this.choiseMemberSwitch = true;
        
      }).catch(err=>{
        console.error(err);
        Loading.getInstance().close();
      });

    },
    
  },
  watch: {
    "moneyData.payMoney": function() {
      // 放钱
      this.moneyData.getMoney = null;
      this.payType = "put";
    },
    "moneyData.getMoney": function() {
      // 拿钱
      this.moneyData.payMoney = null;
      this.payType = "get";
    }
  }
};
</script>

