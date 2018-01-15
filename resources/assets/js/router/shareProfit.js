import ShareProfit from "../view/ShareProfit/ShareProfit.vue"
import ShareProfitRecord from '../view/ShareProfit/profitRecord.vue'
import ShareProfitRecordDetail from '../view/ShareProfit/profitRecordDetail.vue'
import WithDraw from '../view/ShareProfit/widthdraw.vue'

export default [
    {path:'/share_profit/',name:'shareProfit',component:ShareProfit},
    {path:'/profit_record/',name:'shareProfitRecord',component:ShareProfitRecord},
    {path:'/profit_record/detail/',name:'shareProfitRecordDetail',component:ShareProfitRecordDetail},
    {path:'/profit_record/withdraw/',name:'widthdraw',component:WithDraw},    // 提现到个人账户    
]
