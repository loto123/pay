<template>
  <div id="message-list">
      <topBack :title="'消息列表'" style="background:#26a2ff;color:#fff"></topBack>
      <ul class="flex flex-v ">
         
          <li class="flex flex-v flex-justify-around" v-for="item in messageList">
              <div class="notice-content flex flex-align-center flex-justify-around">
                  <div class="user-info flex flex-align-center">
                    <img :src="item.user_avatar" alt="" class="avatar">
                    <span>昵称:{{setString(item.user_name,7)}}</span>
                  </div>
                  <span> {{item.type==0?"申请":"邀请你"}}加入 <i style="color:#26a2ff;">{{setString(item.shop_name,8)}}</i> </span>
              </div>
              <div class="notice-controller flex flex-align-center flex-justify-around">
                  <div>2017-12-1</div>
                  <div>14:55:45</div>
                  <div class="btn-wrap flex flex-align-center flex-justify-around">
                    <span class="cancel" @click="antiItem(item.id)">忽略</span>
                    <span class="agree" @click="agreeItem(item.id)">同意</span>
                  </div>
              </div>
          </li>
      </ul>
      <h3 v-if="!messageList.length">无消息</h3>
      <div class="all-list-controller flex flex-justify-center " v-if="!messageList">
        <div class="btn-wrap flex flex-v flex-justify-around">
          <mt-button type="primary" size="large" style="background:#00cc00;">全部同意</mt-button>
          <mt-button type="primary" size="large" style="background:#ccc;">全部忽略</mt-button>
        </div>
        
      </div>
  </div>
</template>

<style lang="scss" scoped>
#message-list {
  padding-top: 2em;
  padding-bottom:7em;

  ul {
    li {
      height: 6em;
      border-bottom: 0.1em solid #eee;
      .notice-content {
        box-sizing: border-box;
        padding-top: 0.5em;
        .user-info {
          span {
            font-size: 0.75em;
            padding-left: 1.2em;
          }
        }

        > span {
          display: block;
          font-size: 0.75em;
        }

        .avatar {
          display: block;
          border-radius: 0.4em;
          width: 2em;
          height: 2em;
        }
      }
      .notice-controller {
        > div {
          color: #999;
          font-size: 0.9em;
        }
        .btn-wrap {
          width:40%;
          height:100%;
          .cancel {
            background: #ccc;
            color: #fff;
          }
          .agree {
            background: #00cc00;
            color: #fff;
          }
          > span {
            width:40%;
            height: 70%;
            border-radius: 0.3em;
            text-align: center;
            line-height: 2.3em;
          }
        }

        
      }

      > div {
        height: 50%;
      }

      &:nth-child(1) {
        border-top: 0.1em solid #eee;
      }
    }
  }
  h3{
    height: 3em;
    line-height: 3em;
    text-align: center;
  }
  .all-list-controller {
    height: 7em;
    position: fixed;
    width: 100%;
    border-top: 0.1em solid #eee;
    bottom: 0em;
    left: 0em;
    background: #fff;

    .btn-wrap {
      width: 80%;
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack"
import Loading from "../../utils/loading"
import request from "../../utils/userRequest"
import utils from "../../utils/utils"
import {Toast} from 'mint-ui'

export default {
  data(){
    return {
      messageList:[]
    }
  },
  components: { topBack },
  created(){
    this.init();
  },
  methods:{
    init(){
      Loading.getInstance().open();
      request.getInstance().getData("api/shop/messages").then(res=>{
        this.messageList = res.data.data;
        Loading.getInstance().close();
      }).catch(err=>{

      });
    },
    // 同意加入店铺的请求
    agreeItem(id){
      Loading.getInstance().open();
      if(id==null){
        var _data = null;
      }else {
        var _data = {
          id :id
        };
      }
      request.getInstance().postData("api/shop/agree",_data).then(res=>{
        Loading.getInstance().close();
        Toast("操作成功");
        setTimeout(()=>{
          this.init();
        },1500);
      }).catch(err=>{

      });
    },

    antiItem(id){
      Loading.getInstance().open();

      if(id==null){
        var _data = null;
      }else {
        var _data = {
          id :id
        };
      }
      request.getInstance().postData("api/shop/ignore",_data).then(res=>{
        Loading.getInstance().close();
        Toast("操作成功");
        setTimeout(()=>{
          this.init();
        },1500);
      }).catch(err=>{

      });
    },
    setString(str,len){
      return utils.SetString(str,len);
    }
  }
};
</script>


