<template>
    <div class="l-app">
        <div id="nav" class="l-app__sidenav">
            <router-view name="sidenav"/>
        </div>

        <router-view/>
    </div>

</template>

<script>
import { onBeforeMount ,reactive } from 'vue';
import { useStore } from "vuex";

export default {
    //初回ロード時に全ページの情報を取得
    setup(){
        const store = useStore();
        store.dispatch('getPerformances');
        store.dispatch('getLockedFlag');
        store.dispatch('getProcessStatuses');
        store.dispatch('getMentions');
        store.dispatch('getFollowKeywords');
        store.dispatch('getLikeKeywords');
        store.dispatch('getReservedTweets');
        store.dispatch('getTweetedTweets');
        store.dispatch('getMyAccounts');
        store.dispatch('getFollowBases');
        store.dispatch('getProtectedAccounts');
        store.dispatch('checkExistPassword');
        store.dispatch('getEmailAddress');
    }
}
</script>
