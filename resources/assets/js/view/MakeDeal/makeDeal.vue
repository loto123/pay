<template>
  <!-- 发起交易 -->
  <div id = "makeDeal">
    <topBack title="发起交易" style="background:#eee;"></topBack>

    <div class="select-wrap flex flex-align-center" @click="showDropList">
        {{dealShop?dealShop:'请选择您要发起交易的店铺'}}
    </div>

    <div class="price flex">
        <label for="" class="flex-1">设置单价：</label>
        <input type="text" value = "10" class="flex-1" v-model="price">
        <span class="cancer"></span>
    </div>
    
    <div class="textareaWrap">
        <textarea name="" id="" cols="20" rows="3" placeholder = "大吉大利 恭喜发财" v-model="commentMessage">
        </textarea>
    </div>
    
    <div class="notice-wrap flex flex-v">

      <h3 class="flex flex-align-center">添加参与人</h3>
      <div class="flex flex-align-center flex-wrap-on ">
        <img :src="item.avatar" alt="" v-for="item in memberList" :key = "item.id" v-if="item.checked == true">
        <div class="add flex flex-align-center flex-justify-center" @click="showMemberChoise">
          <i class="iconfont" style="font-size: 1.5em;color:#bbb;">
            &#xe600;
          </i>
        </div>
      </div>

    </div>

    <div class="commit-btn">
        <mt-button type="primary" size="large" @click="submitData">确认</mt-button>
    </div>

    <p class="notice">你可以在聊天中发起收付款交易，收到的钱将存入您的结算宝账户中。</p>

    <inputList 
      :showSwitch = "dropListSwitch" 
      v-on:hideDropList="hideDropList" 
      :optionsList = "shopList">
    </inputList>

    <choiseMember 
      :isShow = "choiseMemberSwitch"
      v-on:hide = "hideMemberChoise"
      :dataList = "memberList"
      :submit  ="getMemberData"
    >
    </choiseMember>  
  </div>
</template>

<style scoped lang="scss">
#makeDeal {
  padding-top: 2em;
  background: #eee;
  width: 100%;
  height: 100vh;
  box-sizing: border-box;
  .mint-cell-wrapper {
    background-image: none;
  }
}
.mint-cell-wrapper {
  background-image: none;
}
.mint-cell:last-child {
  background-image: none;
}
.select-wrap {
  width: 90%;
  margin: 0 auto;
  height: 2.5em;
  padding-left: 1em;
  box-sizing: border-box;
  margin-top: 0.5em;
  background: #fff;
}

.price {
  height: 2.5em;
  margin: 0 auto;
  margin-top: 1em;
  width: 90%;
  line-height: 2.5em;
  border-bottom: 1px solid #eee;
  background: #fff;

  label {
    padding-left: 1.2em;
    font-size: 1em;
  }

  input {
    display: block;
    width: 30%;
    font-size: 1.2em;
    outline: none;
    border: none;
    color: #666;
  }

  span {
    display: block;
  }
}

.textareaWrap {
  width: 90%;
  margin: 0 auto;
  margin-top: 1em;

  textarea {
    width: 100%;
    outline: none;
    border: none;
    font-size: 1.2em;
    padding: 1em;
    box-sizing: border-box;
  }
}

.notice-wrap{
  width: 90%;
  height:auto;
  margin:0 auto;
  margin-top:1em;
  background: #fff;

  h3{
    height:1.5em;
    padding-left: 1em;
    color:#555;
    font-size: 0.9em;
  }

  >div{
    min-height: 4em;
    width:100%;
    padding-left: 1em;
    padding-right: 1em;
    box-sizing: border-box;

    img{
      width:2.5em;
      height: 2.5em;
      border-radius: 50%;
      padding:0.3em;
    }

    .add{
      width:2.5em;
      height:2.5em;
      border-radius: 50%;
      border:1px solid #bbb;
      box-sizing: border-box;
    }
  }
}

.commit-btn {
  width: 90%;
  margin: 0 auto;
  margin-top: 1em;
}

.notice {
  text-align: center;
  margin: 0 auto;
  margin-top: 5.5em;
  width: 80%;
  font-size: 0.9em;
}
</style>

<script>
import topBack from "../../components/topBack";
import inputList from "../../components/inputList";
import choiseMember from "./choiseMember.vue"

import Loading from "../../utils/loading";
import request from "../../utils/userRequest";

import utils from "../../utils/utils"

import {Toast} from 'mint-ui'

export default {
  name: "makeDeal",
  created() {
    this.init();
  },
  data() {
    return {
      dropListSwitch: false,       // 下拉框开关
      choiseMemberSwitch:false,    // 选择提醒玩家开关
      dealShop: null,
      shopList: null,

      shopId: null,
      price: 10,
      commentMessage: null,

      memberList:[]              //成员数组
    };
  },

  methods: {
    init() {
      Loading.getInstance().open();
      request
        .getInstance()
        .getData("api/shop/lists/all")
        .then(res => {
          this.setShopList(res);
          Loading.getInstance().close();
        })
        .catch(err => {
          console.error(err);
          Loading.getInstance().close();
        });
    },

    setShopList(res) {
      var _tempList = [];
      for (let i = 0; i < res.data.data.data.length; i++) {
        var _t = {};
        _t.value = res.data.data.data[i].id.toString();
        _t.label = utils.SetString(res.data.data.data[i].name,10);
        _tempList.push(_t);
      }

      this.shopList = _tempList;
    },

    getShopName(id) {
      for (let i = 0; i < this.shopList.length; i++) {
        if (this.shopList[i].value == id) {
          return this.shopList[i].label;
        }
      }

      return "没有这个店铺";
    },

    showDropList() {
      if(this.shopList.length == 0){
        Toast("当前无可选的店铺,请先加入店铺或创建店铺");
        return;
      }

      this.dropListSwitch = true;
    },
    hideDropList(data) {
      this.dropListSwitch = false;
      this.dealShop = this.getShopName(data);
      this.shopId = data;

      this.memberList = [];    // 清空店铺成员列表
    },

    // 获取所有要提醒的成员名单
    showMemberChoise(){
      if(this.shopId == null){
        Toast("请选择发起交易的店铺");
        return;
      }
      Loading.getInstance().open();
      request.getInstance().getData('api/shop/members/'+this.shopId).then(res=>{
        this.initMemberList(res);
        Loading.getInstance().close();

        if(res.data.data.members.length == 0){
          Toast("当前店铺无成员");
          return;
        }

        this.choiseMemberSwitch = true;
        
      }).catch(err=>{
        console.error(err);
        Loading.getInstance().close();
      });

    },

    getMemberData(data){
      this.memberList = data;
    },
    // 初始化提醒玩家列表
    initMemberList(res){

      if(this.memberList.length>0){
        return;
      }

      for(let i = 0; i<res.data.data.members.length; i++){
          var _temp = {};
          _temp = res.data.data.members[i];
          _temp.checked = false;
          this.memberList.push(_temp);
        }
    },

    hideMemberChoise(){
      this.choiseMemberSwitch = false;
    },

    // 提交发起交易的数据
    submitData() {
      var _tempMessage = null;
      if (this.commentMessage == null) {
        _tempMessage = "大吉大利 恭喜发财";
      } else {
        _tempMessage = this.commentMessage;
      }

      var _members = this.getMembersId();

      var _data = {
        shop_id: this.shopId,
        price: this.price,
        comment:_tempMessage,
        joiner:_members
      };

      if(this.shopId == null){
        Toast("请选择发起交易的店铺");
        return 
      }else if(this.price == ""){
        Toast("请设置单价")
        return
      }

      request
        .getInstance()
        .postData("api/transfer/create", _data)
        .then(res => {
          this.$router.push(
            "/makeDeal/deal_detail" + "?id=" + res.data.data.id
          );
        })
        .catch(err => {
          console.error(err);
        });
    },

    getMembersId(){
      if(this.memberList.length == 0){
        return [];
      }

      var _tempIdList = [];

      for(let i = 0; i< this.memberList.length; i++){
        if(this.memberList[i].checked){
          _tempIdList.push(this.memberList[i].id);
        }
      }

      return _tempIdList;
    }
  },
  components: { topBack, inputList , choiseMember }
};
</script>


