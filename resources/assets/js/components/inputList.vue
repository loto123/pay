<template>
  <transition name="slide">
    <div id = "drop-list-component"  v-if="showSwitch">
        <div class="mask" @click="hideTab">

        </div>
        <div class="content">
            <h3 class="flex flex-align-center flex-justify-center">
                {{title?title:"请选择您要发起任务的公会"}}
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
    top:0;
    left: 0;
    z-index: 1001;
    .content{
        left: 10vw;
        top:10vh;
        width:80vw;
        height:70vh;
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
        width:100vw;
        height:100vh;
        position: absolute;
        background: rgba(0,0,0,0.4);
    }
}
</style>

<script>
import utils from '../utils/utils.js'
export default {
  props:["title","showSwitch","optionsList"],

  // showSwitch:组件显示开关
  // optionsList:渲染选择对象
  computed :{
    
  },
  
  created(){
     this.init(); 
  },

  data(){
      return {
        "choiseValue":null,
      }
  },
  methods:{
      hideTab(){
          this.$emit("hideDropList",this.choiseValue);
      },

      init(){
          if(this.choiseValue == null && this.optionsList!=null){
              this.choiseValue = this.optionsList[0].value;
              this.hideTab();
          }
      }
  },
  watch:{
      "choiseValue":function(e){
          this.hideTab();
      }
  }
}
</script>


