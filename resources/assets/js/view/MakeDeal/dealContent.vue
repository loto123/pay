<template>
    <div>
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

       
    </div>

    
</template>

<style lang="scss" scoped>
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


</style>