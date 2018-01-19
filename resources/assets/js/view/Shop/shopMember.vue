<template>
  <div id="shop-member">
      <div class="top">
          <top-back :title="'店铺成员('+membersCount+')'">
          </top-back>
      </div>

      <div id="search-wrap" class="flex flex-align-center">
          <div class="flex flex-align-center flex-justify-around">
              <input type="text" placeholder="搜索" id="search-input" class="flex-7" @keyup="openSearchSwitch" v-model="searchData">
              <button type="button" class="flex-3" v-if="searchSwitch" @click="cancerSearch"> 取消 </button>
          </div>
      </div>
      <ul class="flex flex-wrap-on">

          <!-- <li class="add-member flex flex-v flex-align-center flex-justify-center">
                <div class="img-wrap flex flex-align-center flex-justify-center">
                  <i class="iconfont">
                    &#xe600;
                </i>
              </div>
              <span>
                   
              </span>
          </li> -->

          <li class="minus-member flex flex-v flex-align-center flex-justify-center" @click="openControlSwitch" v-if="isGroupMaster == 1">
              <div class="img-wrap flex flex-align-center flex-justify-center">
                  <i class="iconfont" style="margin-top:-0.2em;">
                    &#xe620;
                </i>
              </div>
              <span>
              </span>
          </li>

          <li class="flex flex-v flex-align-center" v-for="(item,index) in dataList">
              <img :src="item.avatar" alt="" class="avatar">
              <h3>{{SetString(item.name,6)}}  </h3>
              <span class="notice flex flex-align-center flex-justify-center" v-if="controlSwitch &&  index!=0" @click="deleteMember(item.id)">
                - 
              </span>
          </li>
      </ul>
  </div>
</template>

<style lang="scss" scoped>
#shop-member {
  padding-top: 2em;

  ul {
    width: 100%;
    padding-left: 0.5em;
    padding-right: 0.5em;
    box-sizing: border-box;
    
    li {
      width: 25%;
      height: 4.5em;
      box-sizing: border-box;
      position: relative;

      .notice{
        width:1em;
        height: 1em;
        position: absolute;
        background: red;
        right: 0.5em;
        top:0em;
        border-radius: 50%;
        color:#fff;
      }

      .img-wrap {
        width: 3em;
        height: 3em;
        border: 1px solid #aaa;
        border-radius: 0.4em;

        > i {
          font-size: 2em;
          color: #aaa;
        }
      }

      > img {
        width: 2.6em;
        height: 2.6em;
        display: block;
      }

      h3 {
        font-size: 0.5em;
        margin-top:0.2em;
      }

      .avatar{
          box-sizing: border-box;
          margin-top:0.6em;
      }

    }
  }
}

#search-wrap {
  width: 100%;
  height: 3em;
  background: #eee;

  > div {
    width: 95%;
    height: 2em;
    background: #fff;
    margin: 0 auto;
    #search-input {
      border: none;
      outline: none;
      display: block;
      margin: 0 auto;
      height: 100%;
      border-radius: 0.2em;
      padding-left: 2em;
      padding-right: 2em;
      box-sizing: border-box;
      font-size: 1em;
      width: 80%;
      &::-webkit-input-placeholder {
        padding-left: 49%;
        color: #aaa;
      }
    }

    > button {
      border: none;
      outline: none;
      display: block;
      height: 100%;
      background: none;
      font-size: 1em;
      color: #00cc00;
    }
  }
}
</style>

<script>
import topBack from "../../components/topBack";
import Loading from "../../utils/loading.js"
import request from "../../utils/userRequest.js"
import utils from "../../utils/utils.js"
import {Toast} from "mint-ui"

export default {
  created(){
    this.init();
  },

  data() {
    return {
      searchSwitch: false,
      shopId:null,
      membersCount:0,
      dataList:[],
      searchDataList:[],
      searchData:null,  // 玩家搜索的数据
      controlSwitch:false,

      isGroupMaster:0
    };
  },
  components: { topBack },
  methods: {
    // 删除成员
    deleteMember(id){
      Loading.getInstance().open();

      request.getInstance().postData("api/shop/members/"+this.shopId+"/delete/"+id).then(res=>{
        Loading.getInstance().close();
        Toast("删除成功");
        this.controlSwitch = false;
        this.init();
      }).catch(err=>{
          Loading.getInstance().close();
        Toast(err.data.msg);
      });
    },

    // 每次用户输入都执行搜索
    openSearchSwitch() {
      this.searchSwitch = true;

      if(this.dataList.length >0){
        this.searchDataList = [].concat(this.dataList);
      }

      for(var i =0; i<this.searchDataList.length; i++){
        if(this.searchData == this.searchDataList[i].name){
          this.dataList = [];
          this.dataList.push(this.searchDataList[i]);
        }else {
          this.dataList = [];
        }
      }
    },

    openControlSwitch(){
      this.controlSwitch = true;
    },

    init(){
      Loading.getInstance().open();
      this.shopId = this.$route.query.shopId;
      this.isGroupMaster = this.$route.query.isGroupMaster;

      request.getInstance().getData("api/shop/members/"+this.shopId).then(res=>{
        this.dataList = res.data.data.members;
        this.membersCount = res.data.data.count;
        Loading.getInstance().close();
      }).catch(err=>{
        console.error(err);
      });
    },

    SetString(str,len){
      return utils.SetString(str,len);
    },

    cancerSearch(){
      this.searchSwitch = false;
      this.searchData = null;
      this.init();
    }
  }
};
</script>

