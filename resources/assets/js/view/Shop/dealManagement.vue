<template>
  <div id="dealManagement">
      <top-back style="background:#26a2ff;color:#fff;" :title="'交易管理'"></top-back>
        <div id="tab-menu" class=" flex flex-align-center">
            <div class="menu-item flex flex-justify-center flex-align-center " v-bind:class="{active:tabItem[0]}" @click = "changeTab(0)">待结算</div>
            <div class="menu-item flex flex-justify-center flex-align-center" @click = "changeTab(1)" v-bind:class="{active:tabItem[1]}">已平账</div>
            <div class="menu-item flex flex-justify-center flex-align-center" v-bind:class="{active:tabItem[2]}" @click = "changeTab(2)">已关闭</div>
        </div>

        <div class="deal-wrap">
            <ul>
                <li class="timer flex flex-align-center flex-justify-center">
                    <div>
                        2017年11月18日 12:45
                    </div>
                </li>
                <li class="deal-item flex flex-align-center" @click="goDetail">
                    <div class="avatar-wrap flex flex-v flex-align-center flex-2">
                        <img src="/images/avatar.jpg" alt="">
                        <h3>发起人发起</h3>
                    </div>
                    <div class="content-wrap flex flex-v flex-align-center flex-5">
                        <div class="title">交易包中余额:￥168</div>
                        <div class="date">2017-11-18 &nbsp; 14:25:46</div>
                    </div>
                    <div class="pay-detail-wrap flex flex-v flex-align-center flex-3">
                        <div class="title">手续费收益</div>
                        <div class="m-text">￥168</div>
                    </div>
                </li>
                 
                <li class="deal-item flex flex-align-center">
                    <div class="avatar-wrap flex flex-v flex-align-center flex-2">
                        <img src="/images/avatar.jpg" alt="">
                        <h3>发起人发起</h3>
                    </div>
                    <div class="content-wrap flex flex-v flex-align-center flex-5">
                        <div class="title">交易包中余额:￥168</div>
                        <div class="date">2017-11-18 &nbsp; 14:25:46</div>
                    </div>
                    <div class="pay-detail-wrap flex flex-v flex-align-center flex-3">
                        <div class="title">手续费收益</div>
                        <div class="m-text">￥168</div>
                    </div>
                </li>

                <li class="timer flex flex-align-center flex-justify-center">
                    <div>
                        2017年11月18日 12:45
                    </div>
                </li>
                <li class="deal-item flex flex-align-center">
                    <div class="avatar-wrap flex flex-v flex-align-center flex-2">
                        <img src="/images/avatar.jpg" alt="">
                        <h3>发起人发起</h3>
                    </div>
                    <div class="content-wrap flex flex-v flex-align-center flex-5">
                        <div class="title">交易包中余额:￥168</div>
                        <div class="date">2017-11-18 &nbsp; 14:25:46</div>
                    </div>
                    <div class="pay-detail-wrap flex flex-v flex-align-center flex-3">
                        <div class="title">手续费收益</div>
                        <div class="m-text">￥168</div>
                    </div>
                </li>  
                
            </ul>
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
                        margin-top: 0.8em;
                    }

                    .date{
                        color:#999;
                        font-size: 0.9em;
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
            }
        }
    }
}

</style>

<script>
import topBack from "../../components/topBack";
import request from "../../utils/userRequest"
import Loading from "../../utils/loading"

export default {
  components: { topBack },
  created(){
      this.init();
  },
  data(){
    return {
        tabItem:[true,false,false],
        dataList:[]
    }
  },
  methods:{
    changeTab(item){
        if(item>2 || item <0){
            return;
        }
        else {
            this.tabItem = [false,false,false];
            this.tabItem[item] = true;
        }
    },
    goDetail(){
        this.$router.push("/makeDeal/deal_detail");
    },
    init(){
        Loading.getInstance().open();
        var _data = {
            status :1,
            limit :50,
            offset :0
        }

        request.getInstance().getData("api/transfer/record",_data).then(res=>{

        }).catch(err=>{

        });
    }
  }
};
</script>

