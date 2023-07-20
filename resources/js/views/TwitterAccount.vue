<template>
    <div class="l-app__main">
        <div class="p-page">
            <div>
                <div class="p-page__title">
                    <div><i class="p-page__icon u-font_color--mainblue c-icon--shadow fa-solid fa-arrow-right-arrow-left"></i>アカウント切り替え</div>
                </div>
                <div class="p-page__discription">
                    <p>最大10アカウント登録し、管理することができます。</p>
                </div>
            </div>
            <div class="p-change_account">
                <a v-if=" my_accounts.length < max_accounts " class="p-change_account__add c-button c-button--submit" :href="url_twitter_oauth">アカウントを追加</a>
                <span v-else class="p-change_account__reached c-button" >最大数に達しました</span>
            </div>


            <MyAccount v-for="account in my_accounts"
                        v-bind:key="account.record_id"
                        v-bind:info="account"
                        v-bind:url="url_change_account"
                        @put="getAllData"
                        @delete="getAllData"/>
        </div>

    </div>

</template>


<script>
import { computed, onBeforeMount ,reactive } from 'vue';
import { useStore } from "vuex";
import MyAccount from '../Components/MyAccount.vue';

export default {
    components: { MyAccount },
    setup(props){
        const store = useStore();
        const url_twitter_oauth = import.meta.env.VITE_URL_TWITTER_OAUTH;
        const url_change_account = import.meta.env.VITE_URL_CHANGE_ACCOUNT;
        let my_accounts = computed(()=> store.state.my_accounts);
        let max_accounts = computed(()=> store.state.max_accounts);
        //アカウント切り替えのエミットがあった場合は、他のページの情報も全て更新
        const getAllData = ()=>{
            store.dispatch('getPerformances');
            store.dispatch('getLockedFlag');
            store.dispatch('getProcessStatuses');
            store.dispatch('getMentions');
            store.dispatch('getFollowKeywords');
            store.dispatch('getLikeKeywords');
            store.dispatch('getReservedTweets');
            store.dispatch('getTweetedTweets');
            store.dispatch('getFollowBases');
            store.dispatch('getMyAccounts');
            store.dispatch('getProtectedAccounts');
        }


        return { getAllData, my_accounts, max_accounts, url_twitter_oauth, url_change_account };
    }
}
</script>
