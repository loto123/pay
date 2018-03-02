<template>
  <div id="dealManagement">
      <top-back style="background:#26a2ff;color:#fff;" :title="'我的任务'">
          <div class="list-controller flex flex-reverse" 
            style="width:100%;padding-right:1em;box-sizing:border-box;"
            v-if="tabItem[1]"
            @click = "toggleShowListButton"
            >
              {{isListRadioShow?"关闭操作":"操作"}}
          </div>

      </top-back>
        <div id="tab-menu" class=" flex flex-align-center">
            <div class="menu-item flex flex-justify-center flex-align-center " v-bind:class="{active:tabItem[0]}" @click = "changeTab(0)">待结算</div>
            <div class="menu-item flex flex-justify-center flex-align-center" @click = "changeTab(1)" v-bind:class="{active:tabItem[1]}">已完成</div>
            <div class="menu-item flex flex-justify-center flex-align-center" v-bind:class="{active:tabItem[2]}" @click = "changeTab(2)">已关闭</div>
        </div>

        <div class="deal-wrap" ref="wrapper" :style="{ height: wrapperHeight + 'px' }">
            <ul v-bind:class="{wrap:tabItem[1] && isListRadioShow}" v-infinite-scroll="loadMore" infinite-scroll-disabled="loading" infinite-scroll-distance="80">
                <!-- <li class="timer flex flex-align-center flex-justify-center">
                    <div>
                        2017年11月18日 12:45
                    </div>
                </li> -->
                <li class="deal-item flex flex-align-center" @click="goDetail(item.id)" v-for="item in dataList">
                    <div class="avatar-wrap flex flex-v flex-align-center flex-2">
                        <img :src="item.user.avatar" alt="">
                        <h3>{{item.user.name}}</h3>
                    </div>
                    <div class="content-wrap flex flex-v flex-align-center flex-5">
                        <div class="title">任务剩余钻石:{{item.amount}}</div>
                        <div class="date">{{item.created_at}}</div>
                    </div>
                    <div class="pay-detail-wrap flex flex-v flex-align-center flex-3">
                        <div class="title">收益</div>
                        <div class="m-text">
                            {{item.tip_amount}}
                            <i class="diamond" style="float: right;margin-top: 0.1em; margin-left: 0.2em;">&#xe6f9;</i>
                        </div>
                    </div>

                    <div class="controller-wrap flex flex-align-center" v-if="isListRadioShow">
                        <i 
                            class="iconfont flex flex-align-center flex-justify-center" 
                            style="color:#00cc00;" 
                            @click.stop="markItem(item.id)"
                        >
                            {{item.checked? "&#xe6cc;":""}}
                        </i>
                    </div>
                </li>
                 
                <div class="group-controller flex flex-v flex-align-center" v-if="tabItem[1]&&isListRadioShow ">
                    <div class="delete-choise">
                        <mt-button type="primary" size="large" style="background: red;" @click="closeTradementByChoise">关闭选中任务</mt-button>
                    </div>
                    <div class="delete-all">
                        <mt-button type="primary" size="large" style="background: #777;" @click="closeAllTradement">关闭所有已完成的任务</mt-button>
                    </div>
                </div>
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
#dealManagement {
    padding-top: 2em;
    background: #eee;
    min-height: 100vh;
    box-sizing: border-box;

    #tab-menu{
        height: 3em;
        background: #fff;
        width:100%;

        .menu-item{
            height: 100%;
            width:33.33%;
        }

        .active{
            border-bottom:4px solid #26a2ff;
        }
    }
    
    .deal-wrap{
        width:100%;

        ul{
            width:100%;

            .timer{
                width:100%;
                height: 3em;
                margin-top:0.1em;
                >div{
                    display: inline-block;
                    font-size: 0.8em;
                    background:#aaa;
                    color:#fff;
                    padding-left: 0.4em;
                    padding-right:0.4em;
                    padding-top:0.4em;
                    padding-bottom: 0.4em;
                    border-radius: 0.2em;
                }
            }
            .deal-item{
                height: 4em;
                background: #fff;
                width:100%;
                box-sizing: border-box;
                margin-top: 0.1em;
                /*border-bottom:1px solid #eee;*/

                .avatar-wrap{
                    box-sizing: border-box;
                    padding-left: 0.5em;
                    img{
                        width:2.3em;
                        height: 2.3em;
                        border-radius: 0.2em;
                    }
                    h3{
                        font-size: 0.5em;
                        margin-top:0.2em;
                    }
                }
                .content-wrap{
                    height: 100%;
                    .title{
                        font-size: 0.8em;
                        margin-top: 1em;
                    }

                    .date{
                        color:#999;
                        font-size: 0.8em;
                        margin-top:1em;
                    }
                }
                .pay-detail-wrap{
                    height:100%;
                    .title{
                        font-size: 0.9em;
                        margin-top: 1em;
                    }
                    .m-text{
                        font-size: 1.1em;
                        margin-top:0.6em;
                        color:#00cc00;
                    }
                }
                
                .controller-wrap{
                    padding-right: 1em;
                    box-sizing: border-box;
                    height: 100%;

                    >i{
                        width:1.5em;
                        height:1.5em;
                        border-radius:50%;
                        border:1px solid #eee;
                    }
                }
            }
            
            // 批量操作按钮
            .group-controller{
                position: fixed;
                bottom:0em;
                left: 0em;
                width:100%;
                height:7em;
                background: #fff;
                
                .delete-choise{
                
                }

                >div{
                    margin-top:0.5em;
                    width:90%;
                }
            }
        }

        .page-infinite-loading{
            height: 2.5em;
            text-align: center;
        }

        .wrap{
            padding-bottom: 7em;
        }
    }
}

</style>

<script>
import topBack from "../../components/topBack";
import request from "../../utils/userRequest"
import Loading from "../../utils/loading"
import {Toast} from 'mint-ui'

export default {
  components: { topBack },
  created(){
      this.init();
  },
  data(){
    return {
        tabItem:[true,false,false],
        dataList:[],
        isListRadioShow:false,
        shop_id:null,

        wrapperHeight:null,
        loading: false,
        allLoaded: false,
        canLoading:true,
    }
  },
  methods:{
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
          offset :[].concat(this.dataList).pop().id,
          shop_id:this.shop_id,
        }

        request.getInstance().getData('api/transfer/shop',_data).then(res=>{

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
    // 切换面板
    changeTab(item){
        Loading.getInstance().open();
        this.dataList = [];
        this.canLoading = true;
//        this.loading = true;
        this.isListRadioShow = false;
        if(item>2 || item <0){
            return;
        }
        else {
            this.tabItem = [false,false,false];
            this.tabItem[item] = true;
        }
        
        var _data = {
            status :item+1,
            shop_id:this.shop_id,
            limit :50,
            offset :0
        }

        request.getInstance().getData("api/transfer/shop",_data).then(res=>{
            this.dataList = [];
            for(let i = 0; i<res.data.data.data.length; i++){
                var _temp  = res.data.data.data[i];
                _temp.checked = false;
                this.dataList.push(_temp);
            }

            Loading.getInstance().close();
        }).catch(err=>{
            console.error(err);
        });
    },

    goDetail(id){
        this.$router.push("/makeDeal/deal_detail"+"?id="+id);
    },

    toggleShowListButton(){

        if(this.dataList.length == 0){
            Toast("当前无可操作数据...");
            return;
        }
        this.isListRadioShow = !this.isListRadioShow;
    },

    // 标记操作的list对象
    markItem(item){
        var _t = [].concat(this.dataList);

        for(let i = 0 ; i < this.dataList.length; i++){
            if(this.dataList[i].id == item){
                this.dataList[i].checked  = !this.dataList[i].checked;
            }
        }

    },

    // 删除选中的id
    closeTradementByChoise(){

        var _tList = [];
        for(let i = 0 ; i<this.dataList.length ; i++){
            if(this.dataList[i].checked){
                _tList.push(this.dataList[i].id);
            }
        }

        if(_tList.length == 0 ){
            Loading.getInstance().close();
            Toast("当前未选择记录");
            return;
        }

        var _data = {
            transfer_id:_tList,
            shop_id:this.dataList[0].shop_id 
        };
        Loading.getInstance().open();
        request.getInstance().postData('api/transfer/close',_data).then(res=>{
            Loading.getInstance().close();
            Toast(res.data.msg);
            setTimeout(()=>{
                this.init();
            },1500);
        }).catch(err=>{
            Loading.getInstance().close();
            Toast(err.data.msg)
            console.error(err);
        });
    },

    // 删除所有已经完成的任务
    closeAllTradement(){
        Loading.getInstance().open();
        var _data = {
            shop_id:this.dataList[0].shop_id 
        };

        request.getInstance().postData('api/transfer/close',_data).then(res=>{
            Loading.getInstance().close();
            Toast(res.data.msg);
            this.init();

        }).catch(err=>{
            Loading.getInstance().close();
            Toast(err.data.msg)
        });
    },

    init(){
        Loading.getInstance().open();
        this.isListRadioShow = false;
        this.shop_id = this.$route.query.shopId;
        
        var _status = 0;
        for(var i = 0; i<this.tabItem.length; i++){
            if(this.tabItem[i] == true){
            _status = i+1;
            }
        }

        var _data = {
            status :_status,
            shop_id:this.shop_id,
            limit :50,
            offset :0
        }

        request.getInstance().getData("api/transfer/shop",_data).then(res=>{
            this.dataList = [];
            for(let i = 0; i<res.data.data.data.length; i++){
                var _temp  = res.data.data.data[i];
                _temp.checked = false;
                this.dataList.push(_temp);
            }

            Loading.getInstance().close();
        }).catch(err=>{
            Loading.getInstance().close();
        });
    }

  },

  mounted(){
    this.wrapperHeight = document.documentElement.clientHeight - this.$refs.wrapper.getBoundingClientRect().top;
  }
};
</script>

