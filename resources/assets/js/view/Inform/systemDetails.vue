<template>
  <div id="Inform">
    <div id="top">
      <topBack title="消息详情"></topBack>
    </div>
    <div class="details-content">
      <h2>{{title}}</h2>
      <div class="details-date">{{time}}</div>
      <div class="content" v-html="content"></div>
      <div class="btn-wrap flex-3 flex flex-align-center flex-justify-around" v-if="operator_state==1">
          <span v-for="(option,index) in operator_options" :style="{background: option.color}" v-on:click.stop="optionBtn(item.notice_id,index)">{{option.text}}</span>
      </div>
    </div>
  </div>
</template>


<script>
  import axios from "axios";
  import request from '../../utils/userRequest';
  import topBack from "../../components/topBack.vue";
  import { MessageBox, Toast } from "mint-ui";
  
  export default {
    data() {
      return {
        title:null,
        time:null,
        content:null,
        operator_state:null,     //状态
        operator_options:null     //颜色数组
      };
    },
    created() {
      this.details();
    },
    components: { topBack },
    methods: {
      details() {
        var _temp = {};
        _temp.notice_id = this.$route.query.notice_id;
        request.getInstance().getData('api/notice/detail', _temp)
          .then((res) => {
            console.log(res);
            this.title=res.data.data.title;
            this.time=res.data.data.time;
            this.content=res.data.data.content;
            this.operator_state=res.data.data.operator_state;
            this.operator_options=res.data.data.operator_options;
            console.log(this.operator_options);
          })
          .catch((err) => {
            Toast(err.data.msg);
          })
      }
    }
  };
</script>

<style lang="scss" scoped>
  @import "../../../sass/oo_flex.scss";
  #top {
    width: 100%;
    background: #26a2ff;
    color: #fff;
    padding-top: 2em;
    box-sizing: border-box;
  }

  .details-content {
    padding: 1em;
    h2 {
      color: #333;
      font-size: 1.1em;
      margin-bottom: 0.5em;
    }
    .details-date {
      color: #999;
      font-size: 0.9em;
      margin-bottom: 0.8em;
    }
    .content {
      color: #999;
      font-size: 1em;
      width: 100%;
      text-indent: 2em;
      line-height: 1.7em;
      word-break: break-word;
    }
  }
</style>