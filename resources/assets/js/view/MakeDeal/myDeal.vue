<template>
  <div id="my-deal">
      <top-back style="background:#26a2ff;color:#fff;" :title="'我的任务'">
        <div class="mark-wrap flex flex-reverse" @click = "mark">
          {{isStar?"关闭编辑":"标记"}}
        </div>
      </top-back>
        <div id="tab-menu" class=" flex flex-align-center">
            <div class="menu-item flex flex-justify-center flex-align-center " v-bind:class="{active:tabItem[0]}" @click = "changeTab(0)">待结算</div>
            <div class="menu-item flex flex-justify-center flex-align-center" @click = "changeTab(1)" v-bind:class="{active:tabItem[1]}">已完成</div>
            <div class="menu-item flex flex-justify-center flex-align-center" v-bind:class="{active:tabItem[2]}" @click = "changeTab(2)">已关闭</div>
        </div>

        <div class="deal-wrap" ref="wrapper" >
            <ul v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80" v-if="isListShow">
                <!-- <li class="timer flex flex-align-center flex-justify-center">
                    <div>
                        2017年11月18日 12:45
                    </div>
                </li> -->
                
                 <li class="deal-item flex flex-align-center" @click="goDetail(item.transfer_id)" v-for="item in dataList" v-bind:class="{h_5em:tabItem[2]==true}">
                    
                    <div class="content-wrap flex flex-v flex-align-center flex-6">
                        <div class="title">{{SettingString(item.shop_name,10)}}</div>
                        <div class="eggs-wrap" v-if="tabItem[2]">
                          <span>任务获得：</span>
                          <span> <img src="/images/egg.jpg" alt=""> x {{item.eggs}}</span>
                        </div>
                        <div class="date">{{item.created_at}}</div>
                    </div>
                    <div class="pay-detail-wrap flex flex-align-center flex-justify-center flex-3">
                        <div class="m-text">{{item.amount}}<i class="diamond" style="margin-left:0.4em;">&#xe6f9;</i></div>
                    </div>
                    <div class="star-wrap flex flex-align-center flex-justify-center flex-1" @click.stop="markItem(item.id)">
                      <i class="iconfont " v-bind:class="{'edit':isStar}" >
                       {{item.makr?"&#xe708;":""}}
                      </i>
                    </div>
                </li>

            </ul>
            <p v-if="loading" class="page-infinite-loading flex flex-align-center flex-justify-center">
              <!--<span>-->
                <mt-spinner type="fading-circle"></mt-spinner>
                <span style="margin-left: 0.5em;color:#999;">加载中...</span>
              <!--</span>-->
            </p>
        </div>
  </div>
</template>

<style lang="scss" scoped>

.h_5em{
  height: 5em !important;
}

#my-deal {
  padding-top: 2em;
  background: #eee;
  min-height: 100vh;
  box-sizing: border-box;

  .mark-wrap {
    width: 100%;
    box-sizing: border-box;
    padding-right: 1em;
  }

  #tab-menu {
    height: 3em;
    background: #fff;
    width: 100%;

    .menu-item {
      height: 100%;
      width: 33.33%;
    }

    .active {
      border-bottom: 4px solid #26a2ff;
    }
  }

  .deal-wrap {
    width: 100%;

    ul {
      .timer {
        width: 100%;
        height: 3em;
        margin-top: 0.1em;
        > div {
          display: inline-block;
          font-size: 0.8em;
          background: #aaa;
          color: #fff;
          padding-left: 0.4em;
          padding-right: 0.4em;
          padding-top: 0.4em;
          padding-bottom: 0.4em;
          border-radius: 0.2em;
        }
      }
      .deal-item {
        height: 4em;
        background: #fff;
        width: 100%;
        box-sizing: border-box;
        margin-top: 0.1em;
        /*border-bottom:1px solid #eee;*/

      
        .content-wrap {
          height: 100%;
          box-sizing: border-box;
          padding-left: 0.8em;
          .title {
            margin-top: 0.8em;
            width: 100%;
          }
          
          .eggs-wrap{
            width:100%;
            margin-top:0.4em;
            font-size: 0.9em;
            img{
              width:1em;
              height: 1em;
            }
          }

          .date {
            color: #999;
            font-size: 0.9em;
            width: 100%;
            margin-top:0.4em;
          }
        }

        .star-wrap {
          width:100%;
          height:100%;

          i {
            display: block;
            width: 1.5em;
            height: 1.5em;
            border-radius: 50%;
            // background: #26a2ff;
            text-align: center;
            line-height: 1.5em;
            color:#26a2ff;
          }

          .edit{
            border: 1px solid #eee;
          }
        }

        .pay-detail-wrap {
          height: 100%;
          .title {
            font-size: 0.9em;
            margin-top: 1em;
          }
          .m-text {
            font-size: 1.1em;
            color: #00cc00;
          }
        }
      }
    }

    .page-infinite-loading{
      height: 2.5em;
      text-align: center;
    }

  }
}
</style>

<script>
import topBack from "../../components/topBack";
import request from "../../utils/userRequest"
import Loading from "../../utils/loading"
import utils from "../../utils/utils.js"

export default {
  components: { topBack },
  created(){
    this.init();
  },
  data() {
    return {
      isListShow:false,                     // 列表是否显示

      tabItem: [true, false, false],
      isStar:false,
      dataList:[],

      wrapperHeight:null,
      loading: false,
      allLoaded: false,
      canLoading:true,
    };
  },

  mounted(){
    this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
  },
  methods: {
    loadMore() {
      this.loading =false;
      if(this.dataList.length==0 || !this.canLoading){
        return;
      }

      this.loading = true;

      var _status = 0;
      for(var i = 0; i<this.tabItem.length; i++){
        if(this.tabItem[i] == true){
          _status = i+1;
        }
      }

      this.canLoading = false;
      setTimeout(() => {
        var _data = {
          status:_status,
          limit:50,
          offset :[].concat(this.dataList).pop().id
        }

      request.getInstance().getData('api/transfer/record',_data).then(res=>{

        if(res.data.data.data.length == 0){
          this.canLoading = false;
          this.loading = false;
          return;
        }

        for(var i = 0; i< res.data.data.data.length; i ++){
            this.dataList.push(res.data.data.data[i]);
        }

        this.canLoading = true;
        this.loading = false;
      }).catch(err=>{

        });
      }, 1500);

    },

    SettingString(str,len){
      return utils.SetString(str,len);
    },

    changeTab(item) {
      this.isListShow = false;

      if (item > 2 || item < 0) {
        return;
      } else {
        this.canLoading = true;
        this.tabItem = [false, false, false];
        this.tabItem[item] = true;

          Loading.getInstance().open();
          var _data = {
            status:(item+1),
            limit:50,
            offset :0
          }
          request.getInstance().getData('api/transfer/record',_data).then(res=>{
            this.dataList = res.data.data.data;
            Loading.getInstance().close();
            this.isListShow = true;
            
          }).catch(err=>{
            Loading.getInstance().close();
          });
      }
    },
    goDetail(id) {
      this.$router.push("/makeDeal/deal_detail"+"?id="+id);
    },

    mark(){
      if(!this.isStar){
        this.isStar = true;
      }else {
        this.isStar = false;

        Loading.getInstance().open();

        var _mark = [];
        var _disMark = [];
        for(let i = 0; i < this.dataList.length; i++ ){
          if(this.dataList[i].makr == 1){
            _mark.push(this.dataList[i].id);
          }else {
            _disMark.push(this.dataList[i].id);
          }
        }

        var _data = {
          mark : _mark,
          dismark :_disMark
        };

        request.getInstance().postData("api/transfer/mark",_data).then(res=>{
          this.init();
          Loading.getInstance().close();
          
        }).catch(err=>{
        Loading.getInstance().close();

        });
      }
    },

    markItem(id){
      if(!this.isStar){
        return;
      }
      var _temp = [].concat(this.dataList);

      for(let i = 0; i < _temp.length; i++){
        if(id == _temp[i].id){
          if(_temp[i].makr == 0){
            _temp[i].makr = 1;
          }else if(_temp[i].makr == 1){
            _temp[i].makr = 0;
          }
        }
      }     

      this.dataList = _temp;
      
    },
    init(){
      Loading.getInstance().open();
      this.isListShow = false;
      var _status = 0;
      for(var i = 0; i<this.tabItem.length; i++){
        if(this.tabItem[i] == true){
          _status = i+1;
        }
      }
      var _data = {
        status:_status,
        limit:50,
        offset :0
      }
      request.getInstance().getData('api/transfer/record',_data).then(res=>{
        this.dataList = res.data.data.data;
        Loading.getInstance().close();
        this.isListShow = true;
        
      }).catch(err=>{
        Loading.getInstance().close();
      });
      
    },
  }
};
</script>

