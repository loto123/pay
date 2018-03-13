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

            <mt-button type="primary" size="large" @click="callPassword" :disabled="submitClick">确认</mt-button>
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
                
                <span class="info-friend" @click="showMemberChoise" v-if="status!=3 && allow_remind == true">提醒好友</span>
              </div>
            </div>
            
            <ul class="flex flex-v flex-align-center" v-if="isShow">

                <li v-for=" item in recordList">
                    <slider @deleteIt="deleteIt(item.id)" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="item.stat==2 && item.allow_cancel?false:true">

                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <div class="img-wrap flex-2">
                                <img :src=item.user.avatar alt="">
                            </div>

                            <span class="flex-8 infos flex flex-v">
                                <span class="flex" style="margin-left:0.5em;">{{item.user.name}}</span>

                                <span class="flex eggs-info" v-if="status==3 && item.eggs!=0"> 
                                  <span>获得:</span> 
                                  <span class="egg-wrap  flex flex-align-center flex-justify-center">
                                    <img src="/images/egg.jpg" alt="">
                                  </span>
                                  <span class=" flex flex-justify-start">x {{item.eggs}}</span>
                                </span>

                            </span>
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
        
        .infos{
          box-sizing: border-box;
          padding-left: 0.5em;
          padding-right: 0.5em;

          .eggs-info{
            margin-top:0.4em;

            .egg-wrap{
              >img{
                width: 1em;
                height: 1em;
                display: block;
              }
            }

            >span{
              margin-left: 0.5em;
            }
          }

          
        }

        >span {
          display: block;
          text-align: center;
          height: auto;
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
import wx from 'weixin-js-sdk'

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

      balance:0,
      payType: null,              // 支付方式，取钱get 放钱put
      transfer_id:"",             // 任务id
      shop_id:"",
      password:"",                // 支付密码
      allow_reward:false,         // 是否允许打赏
      joiner:[],                  // 任务的参与者，需要提醒的人
      memberList:[],              //成员数组
      
      status:null,                // 1 待结算 2 已平账 3 已关闭
      recordList:[],
      canClick:true,              // 防止连续点击
      submitClick:false,
      choiseMemberSwitch:false,
      allow_remind:true,           // 是否允许提醒其他人
      logo:null
    };
  },
  created() {
    this.init().then(res=>{
      if (res) {
        this.initImage();
        this.shareContent();
      }
    });
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
        this.init();
      }).catch(err=>{
        Toast("撤销失败");
        Loading.getInstance().close();
      });
      
    },

    init() {
      Loading.getInstance().open();
      this.canClick = true;
      this.transfer_id = this.$route.query.id;
      var _data = {
        transfer_id: this.transfer_id
      };
      var data = {
        share_url: window.location.href.split('#')[0],
        list: ['onMenuShareTimeline', 'onMenuShareAppMessage']
      };
      return Promise.all([request.getInstance().getData("api/transfer/show" + "?transfer_id=" + this.transfer_id),request.getInstance().getData("api/index"),request.getInstance().getData("api/proxy/share", data)]).then(res=>{
        this.joiner = res[0].data.data.joiner;
        this.renderData = res[0].data.data;
        this.recordList = res[0].data.data.record;
        this.shop_id = res[0].data.data.shop_id;
        this.allow_reward = res[0].data.data.allow_reward;
        this.isManager = res[0].data.data.allow_cancel;
        this.status = res[0].data.data.status;
        this.allow_remind = res[0].data.data.allow_remind;
        this.isShow = true;

        this.balance = res[1].data.data.balance;
        var Data = res[2].data.data;
        var content=JSON.parse(Data.config);
        wx.config(content);
        return Promise.resolve(true);
        Loading.getInstance().close();
        
      }).catch(err=>{
          console.error(err);
          Toast(err.data.msg);
          Loading.getInstance().close();
          this.$router.push('/404notfound');
      });
    },
    initImage(){
      request.getInstance().getData("api/shop/summary/" + this.shop_id).then(res=>{
        this.logo = res.data.data.logo;
        Loading.getInstance().close();
      }).catch(err=>{
        Toast(err.data.msg);
        Loading.getInstance().close();
      });
    },
    shareContent() {
      let url=window.location.href.split('#')[0];
      let links = url+'/#/makeDeal/deal_detail?id='+this.transfer_id;
      let title = '邀请您加入任务';
      let desc = '任务池的钻石已经放不下啦，还不来拿?';
      let imgUrl = this.logo;
      wx.ready(() => {
        //分享给朋友
        wx.onMenuShareAppMessage({
          title: title, // 分享标题
          desc: desc, // 分享描述
          link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
          imgUrl: imgUrl, // 分享图标
          success: function () {
            // 用户确认分享后执行的回调函数
            Toast('成功分享给朋友');
          },
          cancel: function () {
            // 用户取消分享后执行的回调函数
            Toast('分享失败，您取消了分享');
          }
        })
        //分享到朋友圈
        wx.onMenuShareTimeline({
          title: title, // 分享标题
          link: links, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
          imgUrl: imgUrl, // 分享图标
          success: function () {
            // 用户确认分享后执行的回调函数
            Toast('成功分享到朋友圈');
          },
          cancel: function () {
            // 用户取消分享后执行的回调函数
            Toast('分享失败，您取消了分享');
          }
        })
      })
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
      
      if(this.moneyData.payMoney == null && this.moneyData.getMoney==null){
        Toast("请填写交钻分数或者拿钻分数");
        return;
      }

      var reg = /^\s*(\S+)\s*$/;

      if (!reg.test(this.moneyData.payMoney) ||!reg.test(this.moneyData.getMoney) ) 
      { 
        Toast("金额格式不正确");
        return;
      }

      this.submitClick = true;
      setTimeout(()=>{
        this.submitClick = false;
      },3000);

      if(this.payType == "put"){
        var _put = this.moneyData.payMoney;

        if(_put > this.balance){
          Toast("钻石数量不足");
          return;
        }

        if((parseFloat(_put)).toString().indexOf(".") != -1 || isNaN(Number(_put))){
          this.moneyData.payMoney = null;

          Toast("积分只能是整数");
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
        if(this.moneyData.getMoney == null){
          Toast("请填写交钻分数或者拿钻分数");
        }

        this.submitData();
      }else {
        Toast("请填写交钻分数或者拿钻分数");
      }
    },

    addMembersNotice(dataList){
      if(dataList.length == 0){
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
        this.init();
        
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
              real_amount:res.data.data.real_amount,
              fee_total:res.data.data.fee_total
            }

            return Promise.resolve(_data);
          })
          .then(realData=>{
            MessageBox.confirm("实际拿钻"+ realData.real_amount+ ",手续费" + realData.fee_total+ "钻石").then(action => {

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
      if(this.canClick == false){
        return;
      }
      this.canClick = false;
      var _data = {
        transfer_id:this.transfer_id
      }

      request.getInstance().postData('api/transfer/cancel',_data).then(res=>{
        Toast("撤销任务成功");
        setTimeout(()=>{
          this.$router.push("/makeDeal/my_deal");
        },1500);
      }).catch(err=>{
        Toast(err.data.msg);
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
        Loading.getInstance().close();
        Toast(err.data.msg);
      });

    },
    
  },

  watch: {
    "moneyData.payMoney": function(e) {
      if(e == ""){
        this.moneyData.payMoney = null;
      }
      // 放钱
      this.moneyData.getMoney = null;
      this.payType = "put";
    },
    "moneyData.getMoney": function(e) {
      // 拿钱
      if(e == ""){
        this.moneyData.getMoney = null;
      }

      this.moneyData.payMoney = null;
      this.payType = "get";
    }
  }
};
</script>

