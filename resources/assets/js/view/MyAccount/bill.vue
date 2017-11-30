<template>
    <div id="bill">
       <ul class="bill-list">
           <li class="flex">
             <div class="bill-content">
                 <h5>交易</h5>
                 <div class="time">2017-11-23  19:44:31</div>
             </div>
             <div class="bill-money active">+100</div>
           </li>
           <li class="flex">
             <div class="bill-content">
                 <h5>交易</h5>
                 <div class="time">2017-11-23  19:44:31</div>
             </div>
             <div class="bill-money">+10</div>
           </li>
       </ul>
    </div>
</template>

<script>
export default {
  data() {
    return {};
  }
};
</script>
 
<style lang="scss" scoped>
@import "../../../sass/oo_flex.scss";
.bill-list {
  li {
    height: 55px;
    line-height: 20px;
    border-top: 1px solid #ccc;
    padding: 0 0.7em;
    display: flex;
    align-items: center;
    justify-content: space-between;
    .bill-content {
      .time {
        color: #999;
        font-size: 0.8em;
      }
    }
    .bill-money {
      font-size: 1.2em;
    }
    .active {
      color: #00cc00;
    }
    &:last-child {
      border-bottom: 1px solid #ccc;
    }
  }
}
</style>


