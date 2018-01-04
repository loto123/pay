
import Share from "../view/Share/share.vue"
import ShareUser from "../view/Share/shareUser.vue"
import Generalize from "../view/Share/generalize.vue"
import InviteLink from "../view/Share/inviteLink.vue"
import Download from "../view/Share/download.vue"
export default [
    { path: '/share', name: 'share', component: Share },
    { path: '/shareUser', name: 'ShareUser', component: ShareUser },
    { path: '/shareUser/generalize', name: 'Generalize', component: Generalize },
    { path: '/shareUser/inviteLink', name: 'InviteLink', component: InviteLink },
    { path: '/shareUser/inviteLink/download', name: 'Download', component: Download }
]