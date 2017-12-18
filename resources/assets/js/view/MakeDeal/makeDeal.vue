<template>
  <!-- 发起交易 -->
  <div id = "makeDeal">
    <topBack title="发起交易" style="background:#eee;"></topBack>

    <div class="select-wrap">
        <select name="" id="">
            <option value="" style="color:#999;">请选择您要发起交易的群</option>
            <option value="" >1</option>
            <option value="">2</option>
            <option value="">3</option>
        </select>

    </div>

    <div class="price flex">
        <label for="" class="flex-1">设置单价：</label>
        <input type="text" value = "10" class="flex-1">
        <span class="cancer"></span>
    </div>
    
    <div class="textareaWrap">
        <textarea name="" id="" cols="20" rows="3" placeholder = "大吉大利 恭喜发财">

        </textarea>
    </div>

    <div class="commit-btn">
        <mt-button type="primary" size="large" v-on:click = "confirm">确认</mt-button>
    </div>

    <p class="notice">你可以在聊天中发起收付款交易，收到的钱将存入您的结算宝账户中。</p>

  </div>
</template>

<style scoped lang="scss">
#makeDeal{
    padding-top:2em;
    background:#eee;
    width:100%;
    height: 100vh;
    box-sizing: border-box;
    .mint-cell-wrapper{
        background-image: none ;
    }
}
.mint-cell-wrapper{
    background-image: none;
}
.mint-cell:last-child{
    background-image: none;
}
.select-wrap{
    width:90%;
    height: auto;
    margin:0 auto;
    
    margin-top:0.5em;
    background :#fff;

    select{
       
        display: block;
        box-sizing: border-box;
        color:#666;
        padding-left:1em;
        padding-right:1em;
        border:1px solid #999;
        text-align: center;
        font-size:1em;
        line-height: 1em;
        outline:none;
        height: 2.5em;
        width:100%;
    }

    option{
        border:none;
        outline:none;
        text-align:center;
        color:#000;
    }
}

.price{
    height: 2.5em;
    margin:0 auto;
    margin-top:1em;    
    width:90%;
    line-height: 2.5em;
    border-bottom: 1px solid #eee;
    background:#fff;

    label{
        padding-left:1.2em;
        font-size:1em;
    }

    input{
        display: block;
        width:30%;
        font-size:1.2em;
        outline:none;
        border:none;
        color:#666;
        // text-align: center;
    }

    span{
        display: block;
    }
}

.textareaWrap{
    width:90%;
    margin:0 auto;
   
    margin-top:1em;

    textarea{
        width:100%;
        outline:none;
        border:none;
        font-size:1.2em;
        padding:1em;
        box-sizing: border-box;
    }
}

.commit-btn{
    width:90%;
    margin:0 auto;
    margin-top:1em;

}

.notice{
    text-align: center;
    margin:0 auto;
    margin-top:5.5em;
    width:80%;
    font-size:0.9em;
}
</style>

<script>
import topBack from '../../components/topBack'
import Loading from '../../utils/loading'
import request from '../../utils/userRequest'

export default {
  name:'makeDeal',
  created(){
      this.init();
  },
  methods:{
      confirm(){
          this.$router.push({path:'/makeDeal/deal_detail'})
      },
      init(){
          Loading.getInstance().open();
          request.getInstance().getData('api/shop/lists/all').then((res)=>{
              console.log(res);
              Loading.getInstance().close();
          }).catch((err)=>{
              console.error(err);
              Loading.getInstance().close();
          });
      }
  },
  components:{ topBack }
}
</script>


