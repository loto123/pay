<template>
    <div>
        <section class="content flex flex-v flex-align-center">
            <img :src="renderData.shop_manager.avatar" class="avatar">
            <h3 class="user-name">{{renderData.shop_manager.name}}</h3>
            <p class="transfer-id">任务ID:{{renderData.id}}</p>
            <p class="message">{{(renderData.comment==0)?'无消息':renderData.comment}}</p>
        </section>

        <section class="balance-wrap flex ">
            <div class="flex flex-v flex-align-center flex-justify-around">
                <h3>任务收益倍率</h3>
                <span>{{renderData.price}}</span>
            </div>
            <div class="flex flex-v flex-align-center flex-justify-around">
                <h3>当前任务池钻石</h3>
                <span>{{renderData.amount}}钻石</span>
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
    margin-top: 0.5em;
    color: #666;
  }

  .transfer-id{
    margin-top:0.5em;
    color: #666;
    font-size: 0.9em;
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

<script>
  export default {
    props:["renderData"],
    mounted(){
    }
  }
</script>