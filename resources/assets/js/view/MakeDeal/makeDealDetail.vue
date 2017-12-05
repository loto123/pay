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
                <input type="text">
                    
                </div>
            </div>

            <div class="get-money">
                <label for="">我要拿钱</label>
                <div class="input-wrap">
                <input type="text">
                    
                </div>
            </div>

            <mt-button type="primary" size="large" >确认</mt-button>
        </section>

        <section class="pay-record">

        </section>

    </div>
</template>

<style lang="scss" scoped>

#deal-detail{
    background: #eee;
    height:100vh;
}

.content{
    .avatar{
        display: block;
        width:3.5em;
        height: 3.5em;
        border-radius:50%;
    }

    .user-name{
        margin-top:0.5em;
        font-color:#666;
    }

    .message{
        // font-size:em;
        margin-top:0.5em;
        color:#666;
    }
}

.balance-wrap{
    width:100%;
    height: 4.5em;
    margin-top:0.5em;

    div{
        width:50%;
        h3{
            font-size: 0.9em;
            color:#999;
        }

        span{
            height: 60%;
            font-size: 1.5em;
        }
    }
}

.pay-wrap{
    .pay-money{
        background: #fff;
        width:90%;
        border-radius: 0.2em;
        height:2.5em;
    }
}

</style>


<script>

import topBack from '../../components/topBack'

export default {
  name:'makeDealDetail',
  components:{topBack}
}

</script>

