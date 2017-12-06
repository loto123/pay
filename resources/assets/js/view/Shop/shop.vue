<template>
  <div id="shop">
      <div id="top">
          <topBack>
              <div>hello</div>
          </topBack>

          <div class="imgwrap flex flex-justify-center flex-align-center">
            <i class="iconfont myShop-icon common-icon">
              &#xe61c;
            </i>
          </div>
          
          <h3 class="money-text">689523236.56元</h3>
          <h3>店铺总收益(元)</h3>

      </div>
  </div>
</template>

<style lang="scss" scoped>
#top{
    height:12em;

    width:100%;
    background: #26a2ff;

    .imgwrap{
      width:5em;
      height: 5em;
      background: #fff;
      border-radius: 50%;
      margin: 0 auto;
      margin-top:1em;
      i{
        display: block;
      }
      .myShop-icon{
        font-size: 3.5em;
        color:#26a2ff;
      }
    }

    h3{
      margin-top:0.4em;
      color:#fff;
      text-align:center;
      font-size: 0.9em;
      /*&:nth-child(1){
        background:#fff;
      };*/

    }

    .money-text{
      font-size: 1.3em;
    }

    

}
</style>

<script>
import topBack from '../../components/topBack'

export default {
  components:{topBack}
}
</script>


