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
                <a v-if=" my_accounts.length < max_accounts " class="p-change_account__add c-button c-button--submit" href="https://twitter.com/i/oauth2/authorize?response_type=code&client_id=ZjZHYkRfS0JKLVpWb2NudlJIQTQ6MTpjaQ&redirect_uri=http%3A%2F%2Flocalhost%2Floading&scope=users.read+tweet.read+list.read+like.read+follows.read+follows.write+tweet.write+like.write+offline.access&state=7fLCZLoNqH33-WzLIAFg2QJ6VCaKq6G4GU5MlfnCmCo&code_challenge=JbNNjq_oyqUgUokY8sIs3lrmobD21ZY96Wjrma3661w&code_challenge_method=s256">アカウントを追加</a>
                <span v-else class="p-change_account__reached c-button" >最大数に達しました</span>
            </div>
            
            
            <MyAccount v-for="account in my_accounts"
                        v-bind:key="account.record_id"
                        v-bind:info="account"
                        v-bind:url="'http://localhost/change-account'"
                        @put="getAllData"
                        @delete="getAccounts" />
        </div>
        
    </div>
    
</template>


<script>
import { computed, onBeforeMount ,reactive } from 'vue';
import { useStore } from "vuex";
import MyAccount from '../Components/MyAccount.vue';

export default {
    created: ()=>{
        console.log('CREATED');
    },
    components: { MyAccount },
    setup(props){
        const store = useStore();

        let my_accounts = computed(()=> store.state.my_accounts);
        let max_accounts = computed(()=> store.state.max_accounts);
        const getAccounts = ()=>{
            store.dispatch('getMyAccounts');
        }
        const getAllData = ()=>{
            store.dispatch('getPerformances');
            store.dispatch('getLockedFlag');
            store.dispatch('getProcessStatuses');
            store.dispatch('getMentions');
            store.dispatch('getFollowKeywords');
            store.dispatch('getLikeKeywords');
            store.dispatch('getReservedTweets');
            store.dispatch('getFollowBases');
            store.dispatch('getMyAccounts');
            store.dispatch('getProtectedAccounts');
        }
            
        
        return { getAccounts, getAllData, my_accounts, max_accounts };
    }
}
</script>