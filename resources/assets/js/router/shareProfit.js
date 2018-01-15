import ShareProfit from "../view/ShareProfit/ShareProfit.vue"
import ShareProfitRecord from '../view/ShareProfit/profitRecord.vue'
import ShareProfitRecordDetail from '../view/ShareProfit/profitRecordDetail.vue'

export default [
    {path:'/share_profit/',name:'shareProfit',component:ShareProfit},
    {path:'/profit_record/',name:'shareProfitRecord',component:ShareProfitRecord},
    {path:'/profit_record/detail/',name:'shareProfitRecordDetail',component:ShareProfitRecordDetail},
]
