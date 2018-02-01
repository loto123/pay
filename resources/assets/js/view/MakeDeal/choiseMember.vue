<template>
    <transition name="slide">
        <div id="choise-member" v-if="isShow">
            <div class="top flex flex-align-center">
                <span class="flex-1" @click="hidePage(true)">返回</span>
                <h3 class="flex-4">选择要提醒的成员</h3>
                <div class="flex-1"></div>
            </div>

            <div class="search-wrap flex flex-v flex-align-center flex-justify-center">
                <div class="flex flex-align-center">
                    <i class="iconfont flex-2" style="font-size:1.7em;padding-left:1em;color:#777;margin-top:-0.1em;">
                        &#xe635;
                    </i>

                    <div class="input-wrap flex-8 flex flex-align-center">
                        <input type="text" placeholder="搜索" v-model="searchValue">
                    </div>
                </div>
            </div>

             <ul v-if='isShowList'>
                <li class="flex flex-align-center" v-for="item in searchDataList" :key="item.id" @click="makeMark(item.id)">
                    <span class="img-wrap flex-2">
                        <img :src="item.avatar" >
                    </span>
                    <span class="user-name flex-6">{{item.name}}</span>
                    <span class="flex-2 flex flex-reverse" >
                        <i class="iconfont flex flex-align-center flex-justify-center" style="color:#00cc00;" v-if="!singleMode">
                            {{item.checked? "&#xe6cc;":""}}
                        </i>
                    </span>
                </li>
                
                <li class="flex flex-align-center flex-justify-center" v-if="!dataList.length">当公会无成员</li>
                <!-- <h3 v-if="dataList.length == 0">无数据</h3> -->
            </ul>

            <div class="submit" v-if="!singleMode">
                <mt-button type="primary" size="large" @click="submitData">确认添加</mt-button>
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
  transform: translateX(100vw);
}

#choise-member{
    width:100%;
    height: 100vh;
    position: fixed;
    background: #eee;
    top:0em;
    left: 0em;
    z-index: 1001;

    .top{
        height: 2em;
        padding-left: 1em;
        padding-right: 1em;
        box-sizing: border-box;
        width:100%;

        h3{
            text-align: center;
        }
    }
 
    .search-wrap{
        width:100%;
        height:4em;
        border-bottom:1px solid #ccc;

        >div{
            width:90%;
            height: 2.5em;
            background:#fff;
            border-radius:0.4em;
            .input-wrap{
                box-sizing: border-box;
                padding-right: 1em;
                height:100%;

                >input{
                    height:2.4em;
                    border:none;
                    outline:none;
                    width:100%;
                }
                
                
            }
        }
    }

    ul{
        width:100%;
        height:68%;
        overflow-y: scroll;

        li{
            width:100%;
            height:3em;
            box-sizing: border-box;
            padding-left: 1em;
            padding-right:1em;
            background:#fff;
            margin-top:1em;

            .img-wrap{
                >img{
                    width:2.5em;
                    height:2.5em;
                    border-radius:50%;
                }
            }
            
            i{
                width:1.5em;
                height:1.5em;
                border-radius:50%;
                border:1px solid #eee;
            }
        }
    }

    .submit{
        width:95%;
        margin:0 auto;
        margin-top:0.6em;
    }
}
</style>

<script>
export default{
    /**
     *  参数数据结构
     *  dataList : [
     *      {
     *          avatar:"/images/avatar.jpg",
     *          checked:true
     *          id:1
     *          name:"sa"
     *       }
     *  ]
     */
    data(){
        return {
            localDataList:[],
            searchValue:'',
            searchDataList:null,       // 搜索的结果列表
            isShowList:false
        }
    },
    // isShow :组件开关
    // dataList :渲染数组
    // singleMode :单选模式
    // backUrl : 回退地址
    props:["isShow","dataList","singleMode","backUrl"],
    mounted(){
        this.searchDataList = [].concat(this.dataList);
        this.isShowList = true;
    },

    methods:{
        init(){
            this.searchDataList = [].concat(this.dataList);
            this.isShowList = true;
        },

        hidePage(control){
            if(control && this.$props.backUrl){
                this.$emit("hide",true);
            }else {
                this.$emit("hide");
            }
        },
        submitData(){
            this.$emit("submit",this.localDataList);
            this.hidePage();
        },
        makeMark(id){
            if(!this.singleMode){
                this.localDataList = [].concat(this.$props.dataList);

                for(let i = 0; i< this.localDataList.length; i++){
                    if(this.localDataList[i].id == id){
                        this.localDataList[i].checked = !this.localDataList[i].checked;
                    }
                }
            }else {
                this.localDataList = [].concat(this.$props.dataList);

                for(let i = 0; i< this.localDataList.length; i++){
                    if(this.localDataList[i].id == id){
                        this.localDataList = this.localDataList[i];
                    }
                }
                this.submitData();
            }
           
        },
        search(e){
            if(e.length == 0){
                this.searchDataList = this.dataList;
                return;
            }

            var _tempList = [];
            for(var i = 0; i<this.dataList.length; i++){
                if(e == this.dataList[i].name){
                    _tempList.push(this.dataList[i]);
                }
            }
            this.searchDataList = _tempList;
        }
    },
    watch:{
        "searchValue": function(e){
            this.search(e);
        },

        "dataList":function(){
            this.init();
        }
    }
}

</script>