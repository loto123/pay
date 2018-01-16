<template>
  <div class="card-content flex flex-v flex-justify-between">
    <div class="top-content flex flex-justify-center flex-align-center">
      <div class="flex-8 card-type">
        <div class="type">
          <em>{{cardName}}</em>
        </div>
        <div class="share-profit">尊享分润比例：{{percent}}‰</div>
      </div>
      <div class="flex-2 right-arrow">
        <slot></slot>
      </div>
    </div>
    <div class="bottom-content flex flex-align-center">NO.{{cardNumber}}</div>
  </div>
</template>

<style lang="scss" scoped>
  .card-content{
    height: 100%;
  }
  .top-content {
    height: 80%;
    .card-type {
      text-align: center;
      margin: auto;
      color: #fff;
      .type {
        em {
          font-weight: 700;
          display: inline-block;
          font-size: 1.5em;
          margin-bottom: 0.2em;
        }
      }
    }
    .right-arrow {
      color: #fff;
    }
  }
  .bottom-content{
    height: 20%;
    font-size: 0.8em;
    color: #000;
  }
</style>


<script>
  export default {
    name: "card",
    props: ["cardName", "percent", "cardNumber"],
    methods: {

    }
  };
</script>