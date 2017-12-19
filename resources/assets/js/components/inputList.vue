<template>
  <transition name="slide">
    <div id = "drop-list-component" class="flex flex-align-center flex-justify-center" v-if="showSwitch">
        <div class="mask">

        </div>
        <div class="content">
            <h3 class="flex flex-align-center flex-justify-center">
                请选择您要发起交易的店铺
            </h3>
            <div class="list-wrap">
                 <mt-radio
                    align="right"
                    title=""
                    :options="options">
                </mt-radio>
            </div>
           

            <!-- <ul>
                <li>
                    啦啦啦啦啦啦
                </li>
            </ul> -->
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
    width:100%;
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
            height: 20em;
            overflow: scroll;
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
export default {
  props:["showSwitch"],

  data(){
      return {
        "options":[
            {
                label: '被禁用',
                value: '值F',
                // disabled: true
            },
            {
                label: '选项A',
                value: '值A'
            },
            {
                label: '选项B',
                value: '值B'
            },
            {
                label: '被禁用',
                value: '值F',
                // disabled: true
            },
            {
                label: '选项A',
                value: '值A'
            },
            {
                label: '选项B',
                value: '值B'
            },
            {
                label: '被禁用',
                value: '值F',
                // disabled: true
            },
            {
                label: '选项A',
                value: '值A'
            },
            {
                label: '选项B',
                value: '值B'
            }
        ]
      }
  }
}
</script>


