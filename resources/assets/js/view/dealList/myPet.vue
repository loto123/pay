<template>
        <div id="dealList">
            <topBack title="消息中心" style="background:#26a2ff;color:#fff;" :backUrl="'\/index\/'">
            </topBack>
            <div class="tab-menu flex flex-align-center flex-justify-center">
                <div class="flex flex-align-center flex-justify-center" @click="goSalePet">在售宠物</div>
                <div class="flex flex-align-center flex-justify-center active">我的宠物</div>
            </div>
            <div class="shareBenefit-box">
                <ul>
                    <li class="flex flex-align-center flex-justify-between">
                        
                    </li>
                </ul>
            </div>
        </div>
    </template>
    
    
    <script>
        import request from '../../utils/userRequest';
        import topBack from "../../components/topBack.vue";
        import { MessageBox,Toast } from "mint-ui";
        import Loading from '../../utils/loading'
        export default {
            data() {
                return {
                
                };
            },
            created(){
                
            },
            components: { topBack },
            
            methods: {
                goSalePet(){
                    this.$router.push('/salePet');
                }
            }
        };
    </script>
    
    <style lang="scss" scoped>
        @import "../../../sass/oo_flex.scss";
        #dealList {
            width: 100%;
            padding-top: 2em;
            box-sizing: border-box;
            background: #f4f4f4;
            height: 100vh;
        }
    
        .tab-menu {
            width: 100%;
            height: 3em;
            background: #fff;
            >div {
                width: 50%;
                height: 100%;
                box-sizing: border-box;
            }
            .active {
                border-bottom: 0.2em solid #26a2ff;
                color: #26a2ff;
            }
        }
    </style>