<template>
  <div id="picker-component" >
     <mt-picker 
        :slots="pickerSlots" 
        :showToolbar=true
        :visibleItemCount=5
        :valueKey="'label'"
        @change="change"
      >
      <div class="control-box flex flex-justify-between flex-align-center">
        <div @click="cancel"> 取消</div>
        <div @click="sumbit"> 确认</div>
      </div>
    </mt-picker>
  </div>
</template>

<script>
export default {
  props:["dataList"],
  data(){
    return {
      submitData:null,
      showDataList:null,
      pickerSlots:null
    }
  },
  created(){
    this.init();
  },

  methods:{
    init(){
      if(!this.dataList){
        return;
      }

      this.pickerSlots = [{
        values: this.dataList,
        textAlign: 'center'
      }]
     
    },

    cancel(){
      this.$emit("isShow");
    },

    change(value){
      this.submitData = value.values[0];
    },

    sumbit(){
      this.$emit("isShow",this.submitData.id);
    }
  }
}

</script>

<style scoped lang="scss">

.control-box{
  width: 100%;
  height: 100%;
  padding-left: 1em;
  padding-right: 1em;
  box-sizing: border-box;

  >div{
    color:#26a2ff;
  }
}

#picker-component{
  /*width: 100vw;*/
  background: #fff;
  position: fixed;
  bottom: 0;
  right: 0;
  left: 0;
}
</style>