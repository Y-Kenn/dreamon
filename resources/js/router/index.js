import {createRouter, createWebHistory} from 'vue-router';
import Sidenav from "../Components/Sidenav.vue";
import Home from "../views/Home.vue";
import Follow from "../views/Follow.vue";
import Unfollow from "../views/Unfollow.vue";
import Like from "../views/Like.vue";
import Tweet from "../views/Tweet.vue";
import TwitterAccount from "../views/TwitterAccount.vue";
import Setting from "../views/Setting.vue";
import Withdraw from "../views/Withdraw.vue";


const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/home',
            name: 'home',
            components: {
                default: Home,
                sidenav: Sidenav,
            },
        },
        {
            path: '/follow',
            name: 'follow',
            components: {
                default: Follow,
                sidenav: Sidenav,
            },
        },
        {
            path: '/unfollow',
            name: 'unfollow',
            components: {
                default: Unfollow,
                sidenav: Sidenav,
            },
        },
        {
            path: '/like',
            name: 'like',
            components: {
                default: Like,
                sidenav: Sidenav,
            },
        },
        {
            path: '/tweet',
            name: 'tweet',
            components: {
                default: Tweet,
                sidenav: Sidenav,
            },
        },
        {
            path: '/twitter-account',
            name: 'twitter-account',
            components: {
                default: TwitterAccount,
                sidenav: Sidenav,
            },
        },
        {
            path: '/setting',
            name: 'setting',
            components: {
                default: Setting,
                sidenav: Sidenav,
            },
        },
        {
            path: '/withdraw',
            name: 'withdraw',
            components: {
                default: Withdraw,
                sidenav: Sidenav,
            },
        },

    ]
})

export default router;
