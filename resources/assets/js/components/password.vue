<template>
    <transition name="slide">
        <div id="pass-word-component" v-if="setSwitch" class="flex flex-justify-center">
            <div class="top">
                <topBack :userAction="'closepassword'" v-on:closepassword="closePassword"></topBack> 
            </div>

            <div id="content-wrap" class="flex flex-v flex-align-center">
                <h3 v-if="!settingPasswordSwitch">请输入支付密码</h3>

                <slot>
                  
                </slot>
                <h3 v-if="settingPasswordSwitch">{{secondValid?"请再次输入支付密码":"请设置支付密码"}}</h3>
                <h4 v-if="settingPasswordSwitch&&secondValid">(二次验证)</h4>
                <ul class="flex"  ref="showPassWord">
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                    <li class="flex-1 flex flex-align-center flex-justify-center"></li>
                </ul>
            </div>

            <div class="keyboard">
                <ul class="flex flex-wrap-on">
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">1</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">2</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">3</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">4</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">5</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">6</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">7</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">8</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">9</li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber"></li>
                    <li class="flex flex-align-center flex-justify-center" @click="ipuntNumber">0</li>
                    <li class="flex flex-align-center flex-justify-center" @click="deleteNumber">
                        <i class="iconfont" style="font-size:1.45em;">
                            &#xe601;
                        </i>
                    </li>
                </ul>
            </div>
        </div>
    </transition>
</template>

<style lang="scss" scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.4s ease;
}

.slide-enter,
.slide-leave-to {
  transform: translateY(100vh);
}

#pass-word-component {
  height: 100vh;
  width: 100%;
  background: #efeff4;
  position: fixed;
  top: 0em;
  left: 0em;
  z-index: 1001;

  .top {
    padding-top: 2em;
  }

  #content-wrap {
    height: auto;
    width: 75%;
    margin-top: 4em;

    h3 {
      font-size: 1.5em;
    }
    
    h4{
      margin-top:0.5em;
      color:#555;
      font-size: 0.9em;
    }

    ul {
      width: 100%;
      height: 3em;
      margin-top: 1.5em;
      li {
        background: #fff;
        box-sizing: border-box;
        border-right: 1px solid #aaa;
        border-top: 1px solid #aaa;
        border-bottom: 1px solid #aaa;
        font-weight: bold;
        font-size: 1.5em;
        color: #888;

        &:nth-child(1) {
          border-left: 1px solid #aaa;
        }
      }
    }
  }

  .keyboard {
    width: 100%;
    height: 9em;
    position: fixed;
    bottom: 0;
    left: 0;
    font-size: 1.5em;
    color: #888;
    ul {
      width: 100%;
      height: 100%;
      li {
        background: #fff;
        width: 33.33%;
        height: 25%;
        box-sizing: border-box;
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;

        &:nth-child(3n) {
          border-right: none;
        }

        &:nth-child(1) {
          border-top: none;
        }

        &:nth-child(2) {
          border-top: none;
        }

        &:nth-child(3) {
          border-top: none;
        }
      }
    }
  }
}
</style>

<script>
import topBack from "./topBack";

export default {
  components: { topBack },
  data() {
    return {
      password: ""
    };
  },
  
  // validationData data url methond
  // setSwitch : 组件显示开关
  // validationData : 需要验证的数据
  // settingPasswordSwitch : 设置密码开关
  props: ["setSwitch","validationData","settingPasswordSwitch","secondValid"],
  methods: {
    ipuntNumber(e) {
      if (this.password.length >= 6) {
        return;
      }

      this.password += e.srcElement.innerHTML;
      this.setPassword();

      if (this.password.length >= 6) {
        // this.closePassword();
        this.doCallback();
      }
    },

    setPassword() {
      var liElement = this.$refs.showPassWord.children;

      for (var j = 0; j < this.$refs.showPassWord.children.length; j++) {
        this.$refs.showPassWord.children[j].innerHTML = "";
      }

      for (var i = 0; i < this.password.length; i++) {
        this.$refs.showPassWord.children[i].innerHTML = "*";
      }
    },
    deleteNumber() {
      var len = this.password.length;
      if (len == 0) {
        return;
      }
      this.password = this.password.substring(0, len - 1);
      this.setPassword();
    },
    closePassword(){
        this.$emit("hidePassword");
        this.password = "";
    },
    // 走回调
    doCallback(){
        this.$emit("callBack",this.password);
        this.password = "";
        this.setPassword();
    }

  }
};
</script>

