<template>
  <div id="my-vip">
      <div class="top flex flex-v flex-align-center">
          <topBack 
            style="color:#fff;background:#26a2ff;"
            :title="'我的VIP'"
          >
          </topBack>

          <div class="imgwrap">
            <img src="/images/avatar.jpg" alt="">
          </div>

          <h3>Leaf (未开通)</h3>

          
      </div>
      <div class="infos flex flex-align-center">
          <h3 v-bind:class="[isBindVIP?'goldFont':'redFont']">{{isBindVIP?'受益于您的VIP权益，您获得的分润收益将提高至5‰':'您还没有绑定VIP卡，绑定VIP卡后可实现收益翻倍！'}}</h3>


      </div>

      <ul>
          <li class="flex flex-align-center">
              <span class="flex-9">
                什么是VIP卡？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
          </li>

          <li class="flex flex-align-center">
              <span class="flex-9">
                绑定VIP卡的好处？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
          </li>

          <li class="flex flex-align-center">
              <span class="flex-9">
                如何获得VIP卡？
              </span>
              <span class="flex-1">
                  <i class="iconfont" style="color:#999;">
                      &#xe62e;
                  </i>
              </span>
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
    }
}
</style>

<script>
import topBack from '../../components/topBack'

export default {
  components:{topBack},
  data(){
      return {
          isBindVIP:false
      }
  },
}
</script>


