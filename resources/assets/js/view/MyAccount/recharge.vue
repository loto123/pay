<template>
    <div id="purchase" class="purchase-container">
        <topBack title="购买" style="background: #eee;">
            <div class="flex flex-reverse" style="width:100%;padding-right:1em;box-sizing:border-box;" @click="goIndex">
                <i class="iconfont" style="font-size:1.4em;">&#xe602;</i>
            </div>
        </topBack>
        <div class="purchase-box">
            <div class="price-list-box">
                <div class="tltle">选择要购买的宠物价格</div>
                <ul class="price-list flex flex-wrap-on">
                    <li v-for="(item,index) in moneyList" :class='{active:index===number}' @click="change(index,item)">￥{{item}}</li>
                </ul>
            </div>

            <div class="pet-list-box">
                <div class="header flex flex-align-center">
                    <div class="title">符合购买价格的宠物</div>
                    <div class="query-btn" @click="queryPetBtn">
                        <mt-button type="primary" size="small">查询</mt-button>
                    </div>
                </div>
                <ul class="cur-pet">
                    <li class="flex flex-align-center">
                        <img :src="pic">
                    </li>
                </ul>	
            </div>
            <div class="purchase-way">
                <div class="title">选择购买方式</div>
                <div class="list-wrap">
                    <mt-radio align="right" title="" v-model="value" :options="options1">
                    </mt-radio>
                </div>
            </div>
            <a href="javascript:;" class="purchase-btn" @click="purchaseBtn">
                <mt-button type="primary" size="large">购买</mt-button>
            </a>
        </div>
    </div>
</template>

<script>
    import request from '../../utils/userRequest';
    import topBack from "../../components/topBack.vue";
    import Loading from "../../utils/loading";
    import { MessageBox, Toast } from "mint-ui";

    export default {
        data() {
            return {
                options1: [],
                way: null,
                value: null,
                number:null,
                moneyList:[],
                curPrice:null,
                pic:null,		//获取到的宠物
                picId:null,		//宠物id
                hatching:null,	// true 在孵化中  false  孵化成功
                timer:null,
                billId:null
            }
        },
        created() {
            this.init();
        },
        components: { topBack },
        props: ["showSwitch", "optionsList"],
        methods: {
            goIndex() {
                this.$router.push("/index");
            },
            init() {
                Promise.all([request.getInstance().getData('api/account/pay-methods/unknown/2'),request.getInstance().getData('api/account/deposit_quotas')])
                    .then((res) => {
                        this.setPurchaseList(res[0]);
                        this.moneyList=res[1].data.data.quota_list;
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                    })
            },
            setPurchaseList(res) {
                var _tempList = [];
                for (let i = 0; i < res.data.data.methods.length; i++) {
                    var _t = {};
                    _t.value = res.data.data.methods[i].id.toString();
                    _t.label = res.data.data.methods[i].label;
                    _tempList.push(_t);
                }
                this.options1 = _tempList;
            },
            change(index,content){  
                this.number= index;  
                this.curPrice=content;
                this.pic=null;
            },
            queryPetBtn(){
                if(!this.curPrice){
                    Toast('请选择要购买的宠物价格')
                    return
                }
                var _data = {
                    price : this.curPrice
                }
                request.getInstance().postData('api/pet/on_sale', _data)
                    .then((res) => {
                        this.picId=res.data.data.pet_id;
                        this.hatching=res.data.data.hatching;
                        this.billId=res.data.data.id
                        if(this.hatching==true){
                            Loading.getInstance().open();
                            this.timer = setTimeout(() => {
                                this.refresh();
                                if(this.hatching==false){
                                    clearTimeout(this.timer);
                                    Loading.getInstance().close();
                                }
                            }, 500)
                        }else{
                            this.pic=res.data.data.pic;
                            Loading.getInstance().close();
                        }
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            refresh(){
                var _data = {
                    pet_id: this.picId
                }
                request.getInstance().postData('api/pet/refresh_pet', _data)
                    .then((res) => {
                        this.hatching=res.data.data.hatching;
                        if (this.hatching==false) {
                            this.pic=res.data.data.pic;    
                        }else{
                            this.timer = setTimeout(() => {
                                Loading.getInstance().open();
                                this.refresh();
                                if(this.hatching==false){
                                    clearTimeout(this.timer);
                                    Loading.getInstance().close();
                                }
                            }, 500)
                        }
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            },
            purchaseBtn() {
                if(!this.pic){
                    Toast('请先通过价格来查询您所需要的宠物')
                    return
                }else if(!this.value){
                    Toast('请选择购买方式')
                    return
                }
                var self = this;
                var _data = {
                    bill_id :this.billId,
                    way: this.value
                }
                Loading.getInstance().open();
                request.getInstance().postData('api/account/charge', _data)
                    .then((res) => {
                        location.href = res.data.data.redirect_url;
                        Loading.getInstance().close();
                    })
                    .catch((err) => {
                        Toast(err.data.msg);
                        Loading.getInstance().close();
                    })
            }
        }
    };
</script>

<style lang="scss" scoped>
    @import "../../../sass/oo_flex.scss";
    .purchase-container {
        background: #eee;
        height: 100vh;
        padding-top: 2em;
        box-sizing: border-box;
    }

    .purchase-box {
        background: #fff;
        padding: 1em;
        margin: 0 0.5em;
    }

    .price-list-box {
        .tltle {
            color: #666;
        }
        .price-list {
            width: 100%;
            overflow: hidden;
            li {
                width: 28%;
                line-height:2.5em;
                border: 1px solid #ccc;
                float: left;
                border-radius: 5px;
                margin-top: 1em;
                text-align: center;
                margin-left:7%;
                &:nth-child(3n+1) {
                    margin-left: 0;
                }
            }
            .active{
                color: #00CC00;
                border: 1px solid #00CC00;
            }
        }
    }
    .pet-list-box{
        margin-top:1em;
        .header{
            margin-bottom:0.3em;
            .title{
                margin-right:0.5em;
                color:#666;
            }
        }
        .cur-pet{
            height: 8em;
            border: 1px solid #eee;
            width: 100%;
            li{
                height: 100%;
                padding-left:0.5em;
                img{
                    display: inline-block;
                    width:8em;
                }
            }
        }
    }
    .purchase-way {
        margin-top: 2em;
    }

    .purchase-btn {
        display: block;
        margin-top: 3em;
        margin-bottom: 1em;
    }
</style>