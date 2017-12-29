<template>
  <transition name="slide">
    <div id = "drop-list-component" class="flex flex-align-center flex-justify-center" v-if="showSwitch">
        <div class="mask" @click="hideTab">

        </div>
        <div class="content">
            <h3 class="flex flex-align-center flex-justify-center">
                请选择您要发起交易的店铺
            </h3>
            <div class="list-wrap">
                 <mt-radio
                    align="right"
                    title=""
                    v-model="choiseValue"
                    :options="optionsList"
                    style="font-size: 0.9em;"
                  >
                </mt-radio>
            </div>
            
            <!-- <div class="loading-more flex flex-align-center flex-justify-center">
                加载更多...
            </div> -->
        </div>

        
    </div>  
  </transition>       
</template>

<style lang="scss" scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter,
.slide-leave-to {
  opacity: 0;
}

#drop-list-component{
    height:100vh;
    width:100vw;
    position: fixed;
    top:0em;
    left: 0em;
    z-index: 1001;
    .content{
        width:80%;
        height:70%;
        background:#fff;
        position: absolute;
        padding-left: 1em;
        padding-right:1em;
        box-sizing: border-box;

        h3{
            height: 3em;
        }
        
        .list-wrap{
            height:70%;
            overflow: scroll;
        }
        
        .loading-more{
            height:3em;
            width:100%;
            padding-left: 1em;
            box-sizing: border-box;
        }
    }

    .mask{
        width:100%;
        height:100%;
        position: absolute;
        background: rgba(0,0,0,0.4);
    }
}
</style>

<script>
import utils from '../utils/utils.js'
export default {
  props:["showSwitch","optionsList"],

  // showSwitch:组件显示开关
  // optionsList:渲染选择对象
  computed :{
    
  },
  data(){
      return {
        "choiseValue":null,
        "shopList":null
      }
  },
  methods:{
      hideTab(){
          this.$emit("hideDropList",this.choiseValue);
      },
  },
  watch:{
      "choiseValue":'hideTab'
  }
}
</script>


