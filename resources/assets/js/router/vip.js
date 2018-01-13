import MyVip from '../view/Vip/myVip.vue' 
import VipCard from '../view/Vip/vipCard.vue' 
import Generalize from '../view/Vip/generalize.vue'
import OpenCard from '../view/Vip/openCard.vue'
import GiveCard from '../view/Vip/giveCard.vue'
export default [
    {path:'/my_vip',name:'myVip',component:MyVip},
    {path:'/vipCard',name:'vipCard',component:VipCard},
    {path:'/generalize',name:'generalize',component:Generalize},
    {path:'/vipCard/openCard',name:'openCard',component:OpenCard},
    {path:'/vipCard/giveCard',name:'giveCard',component:GiveCard}
]