<template>
  <div id="my-vip" >
      <div class="top flex flex-v flex-align-center">
          <topBack 
            style="color:#fff;background:#26a2ff;"
            :title="'我的VIP'"
          >
          </topBack>

          <div class="imgwrap">
            <img :src="avatar" alt="">
          </div>

          <h3>{{userName}} ({{isBindVIP?'已开通':'未开通'}})</h3>
          
      </div>
      <div class="infos flex flex-align-center" v-if="isShow">
          <h3 v-bind:class="[isBindVIP?'goldFont':'redFont']">{{isBindVIP?'受益于您的VIP权益，您获得的分润收益将提高至'+percent+'‰':'您还没有绑定VIP卡，绑定VIP卡后可实现收益翻倍！'}}</h3>
      </div>
      
      <div class="card-wrap" v-if="isBindVIP">
          <Card
            style="height:10em;"
            :cardName="cardName"
            :cardNumber="cardNumber"
            :percent="percent"
          >
            <div class="card-tag flex flex-v flex-align-center">
                <h3>(已绑定)</h3>
                <div class="tag flex flex-justify-center flex-align-center">
                    <span>终身有效</span>
                </div>
            </div>
          </Card>
      </div>

      <ul>
          <li class="flex flex-align-center" @click="changeInfoStatus(0)">
              <span class="flex-9">
                什么是VIP卡？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
          </li>

          <li class="info" v-if="infoStatus[0] == true">
              <p>VIP卡是尊贵身份的象征，同时，绑定VIP卡的代理将享受更高的分润收益。</p>
          </li>

          <li class="flex flex-align-center" @click="changeInfoStatus(1)">
              <span class="flex-9">
                绑定VIP卡的好处？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
          </li>
          
          <li class="info" v-if="infoStatus[1]">
              <p> 绑定黄金VIP卡的代理将享受额外xx‰的分润收益，且终身有效。</p>
          </li>

          <li class="flex flex-align-center" @click="changeInfoStatus(2)">
              <span class="flex-9">
                如何获得VIP卡？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
          </li>
          <li class="info" v-if="infoStatus[2]">
              <p> 可联系相应的推广员开通绑定VIP卡。</p>
          </li>
          
      </ul>
  </div>
</template>

<style lang="scss" scoped>
#my-vip{
    padding-top:2em;
    background: #eee;
    min-height:100vh;
    box-sizing: border-box;

    .top{
        width: 100%;
        height: 8em;
        background: #26a2ff;

        .imgwrap{
            width:4em;
            height:4em;
            margin-top:0.5em;
            // border-radius:0.4em;
            >img{
                width:100%;
                height: 100%;
                border-radius:50%;
            }
        }

        >h3{
            padding-top:0.5em;
            padding-bottom: 0.5em;
            color:#fff;
        }       
    }
    .infos{
        width:100%;
        height: 3em;
        background:#fff;

        >h3{
            font-size:0.85em;
            text-align: center;
            width:100%;
            display: block;
        }

        .redFont{
            color:red;
        }
        .goldFont{
            color: #F5CB34;
        }
    }

    .card-wrap{
        background: url("/images/vipBack2.png") no-repeat;
        background-size: 100% 100%; 
        box-sizing:border-box;
        width: 95%;
        margin: 0 auto;
        margin-top:1em;
        margin-bottom: 0.5em;

        .card-tag{
            width:100%;
            height: 7em;
            padding-right:1em;
            box-sizing:border-box;

            h3{
                margin-top:1em;
                color:#fff;
            }
            .tag{
                width:4em;
                height: 4em;
                background:#fff;
                border-radius:50%;
                margin-top:0.6em;
                
                span{
                    width:50%;
                    height: 50%;
                    display: block;
                    color:#F5CB34;
                }
            }
        }
    }

    ul{
        li{
            padding-left: 1em;
            padding-right: 1em;
            box-sizing: border-box;
            height: 2.5em;
            background:#fff;
            width:100%;
            margin-top:0.5em;
        }

        .info{
            background: #eee;
        }
    }
}
</style>

<script>
import topBack from '../../components/topBack'
import request from '../../utils/userRequest'
import Loading from '../../utils/loading'
import Card from '../../components/card'

export default {
  components:{topBack,Card},
  data(){
      return {
          isBindVIP:false,
          isShow:false,
          cardName:null,
          cardNumber:null,
          percent:null,

          userName:null,
          avatar:null,

          infoStatus:[false,false,false]
      }
  },

  mounted(){
    this.init();
  },
  methods:{
      init(){
          Loading.getInstance().open();

          Promise.all([request.getInstance().getData("api/my/info"),request.getInstance().getData('api/agent/bound_vip')]).then(res=>{
              this.userName = res[0].data.data.name;
              this.avatar = res[0].data.data.thumb;
              
                  try{
                    this.isBindVIP =  res[1].data.data.if_bound;
                    this.cardName = res[1].data.data.card_name;
                    this.cardNumber = res[1].data.data.card_no;
                    this.percent = res[1].data.data.percent;
                    }catch(e){
                        console.error(e);
                    }
              
              this.isShow = true;
              Loading.getInstance().close();
          }).catch(err=>{
              console.error(err);
          });
      },
      
      changeInfoStatus(index){
          console.log(this.infoStatus);
          if(this.infoStatus[index] == true){
              this.infoStatus = [this.infoStatus[0] ,this.infoStatus[1] ,this.infoStatus[2]];
              this.infoStatus[index] = false;
              return ; 
          }
          this.infoStatus = [this.infoStatus[0] ,this.infoStatus[1] ,this.infoStatus[2]];
          this.infoStatus[index] = true;
          console.log(this.infoStatus);
      }
  }
}
</script>
