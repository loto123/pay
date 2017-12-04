<template>
    <div id="deal-detail">
        <topBack></topBack>

        <section class="content flex flex-v flex-align-center">
            <img src="/images/avatar.jpg" alt="" class="avatar">
            <h3 class="user-name">看看我的名字是不是很长</h3>
            <p class="message">玩家留言  大吉大利  晚上吃鸡</p>
        </section>

        <section class="balance-wrap flex ">
            <div class="flex flex-v flex-align-center flex-justify-around">
                <h3>当前倍率</h3>
                <span>10</span>
            </div>
            <div class="flex flex-v flex-align-center flex-justify-around">
                <h3>当前钱包中的余额</h3>
                <span>288.66元</span>
            </div>
        </section>

        <section class="pay-wrap flex flex-v flex-align-center">

            <div class="pay-money flex flex-align-center flex-justify-around">
                <label for="">我要付钱</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数">
                </div>
            </div>

            <div class="get-money flex flex-align-center flex-justify-around">
                <label for="">我要拿钱</label>
                <div class="input-wrap">
                    <input type="text" placeholder="请输入您的分数">
                </div>
            </div>

            <mt-button type="primary" size="large" >确认</mt-button>
        </section>
        
        <!-- 参与玩家记录 -->
        <section class="pay-record ">
            <div class="title">
                <span>3人参与</span>
                <span class="info-friend">提醒好友</span>
            </div>
            
            <ul class="flex flex-v flex-align-center">
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="true">
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v flex-justify-between flex-align-center">
                                <span class="money">-100</span>
                                <span class="title">付钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
                
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" >
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v  ">
                                <span class="money green-color">+100</span>
                                <span class="title">拿钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
                <li>
                    <slider @deleteIt="deleteIt" v-bind:height="'3em'" v-bind:actionUser="'撤销'" v-bind:able="true">
                        <div class="slider-item flex flex-align-center flex-justify-between">
                            <img src="/images/avatar.jpg" alt="">
                            <span>名字最多七个字</span>
                            <div class="pay-money-text flex flex-v flex-justify-between flex-align-center">
                                <span class="money">-100</span>
                                <span class="title">付钱</span>
                            </div>
                        </div>
                    </slider> 
                </li>
            </ul>
        </section>

        <section id="qrcode" class="flex flex-justify-center"></section>
        <h3 class="notice">扫描二维码快速交易</h3>
    </div>
</template>

<style lang="scss" scoped>
.green-color{
    color:green;
}

#deal-detail {
  background: #eee;
  min-height: 100vh;
  /*padding-bottom:1em;*/
}

.content {
  .avatar {
    display: block;
    width: 3.5em;
    height: 3.5em;
    border-radius: 50%;
  }

  .user-name {
    margin-top: 1em;
    font-color: #666;
  }

  .message {
    // font-size:em;
    margin-top: 0.5em;
    color: #666;
  }
}

.balance-wrap {
  width: 100%;
  height: 4.5em;
  margin-top: 1em;

  div {
    width: 50%;
    h3 {
      font-size: 0.9em;
      color: #999;
    }

    span {
      height: 60%;
      font-size: 1.5em;
    }
  }
}

.pay-wrap {
  .pay-money {
    background: #fff;
    width: 90%;
    border-radius: 0.2em;
    height: 2.5em;

    label {
      width: 40%;
      padding-left: 0.5em;
      padding-right: 0.5em;
      box-sizing: border-box;
    }

    .input-wrap {
      height: 100%;
      width: 60%;
      input {
        box-sizing: border-box;
        font-size: 1.2em;
        padding-left: 0.5em;
        width: 100%;
        border: none;
        outline: none;
        height: 100%;
      }
    }
  }

  .get-money {
    @extend .pay-money;
    margin-top: 1em;
  }
}

.mint-button {
  margin-top: 1em;
  width: 90%;
}

.pay-record {
    padding-top:0.5em;
    .title{
        /*margin-top:1em;*/
        width:90%;
        height: 2em;
        line-height: 2em;

        margin: 0 auto;
        >span{
            float:  left;
        }
    
        .info-friend{
            float:right;
            background: green;
            color:#fff;
            padding-left: 0.3em;
            padding-right: 0.3em;
            border-radius: 0.3em;
            font-size:0.9em;
        }

    }

  ul {
    margin-top:0.5em;
    li {
      /*overflow :hidden;*/
      margin-top: 0.2em;
      width: 90%;
      .slider-item {
        box-sizing: border-box;
        padding-left: 0.5em;
        padding-right: 0.5em;
        height: 3em;
        /*padding-top: 0.4em;*/

        .pay-money-text{
            width:20%;
            height: 100%;
            .money{
                font-size: 1.1em;
                width:100%;
                line-height: 2em;
                height: 50%;
            }

            .title{
                font-size:0.9em;
                height: 50%;
                color:#999;
                width: 100%;
            }

        }

        img {
          width: 2.5em;
          height: 2.5em;
          display: block;
        }

        span {
          display: block;
          text-align:center;
        }
    

      }
      /*#slider-component {
        margin-top: 0.5em;
      }*/
    }
  }
}

#qrcode{
    margin-top:1.5em;
}

.notice{
    margin-top:1em;
    padding-bottom: 1.5em;
    text-align: center;
    font-size: 0.9em;
    color:#999;
}
</style>


<script>
import topBack from "../../components/topBack";
import slider from "../../components/slider";

import qrCode from "../../utils/qrCode"

console.log(qrCode);
export default {
  name: "makeDealDetail",

  mounted(){
    this._getQRCode()
  },
  methods: {
   
    deleteIt() {
      console.log("i m ru");
    },
    _getQRCode: function() {
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width : 100,//设置宽高  
            height : 100
        });
        // document.getElementById("getval").onkeyup =function(){
        //     qrcode.makeCode(document.getElementById("getval").value);
        // };

        qrcode.makeCode("http://www.baidu.com");

            },
  },
  components: { topBack, slider }
};
</script>

