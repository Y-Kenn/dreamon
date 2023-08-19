<template>
    <div class="l-app__main">
        <div class="p-page">
            <div class="p-page__title">
                <div><i class="p-page__icon u-font_color--mainblue c-icon--shadow fa-solid fa-user-plus"></i>フォロー</div>
            </div>
            <div class="p-page__discription">
                <p>自動でいいねを付けたアカウントに対し、フォローキーワードのいずれかににヒットする場合に自動でフォローします。</p>
                <p>フォローキーワードを設定しない場合、いいねを付けた全てのアカウントをフォローします。</p>
                <p class="u-underline">自動フォローを使用するには自動いいねを起動している必要があります。</p>
            </div>

            <div class="p-activate">
                <button @click="toggleStatus" v-bind:class="{ 'p-activate__button--active': status.status }" class="c-button p-activate__button">{{ (status.status) ? '自動フォロー中' : '自動フォロー開始' }}</button>
            </div>

            <div class="p-page__sub_title">
                <i class="fa-solid fa-square u-margin--right--5px"></i>キーワード
            </div>
            <div class="p-page__discription">
                <p>スペースで区切って同時に複数のキーワードを登録した場合、その全てのキーワードがヒットしたアカウントのみフォローします。
                    除外指定したキーワードにヒットするアカウントはフォローされません。</p>
            </div>
            <FollowKeyword />
        </div>

    </div>
</template>

<script>
import { onBeforeMount , reactive, computed } from 'vue';
import FollowKeyword from '../Components/FollowKeyword.vue';
import FollowBase from '../Components/FollowBase.vue';
import { useStore } from "vuex";

export default {
    components: { FollowKeyword, FollowBase },
    setup(){
        const store = useStore();
        let status = computed(()=> store.state.process_statuses.find(proc => proc.id === 0));
        //自動フォローの稼働状況(稼働中or停止中)を取得
        const getStatus = ()=>{
            store.dispatch('getProcessStatuses');
        }
        //稼働状況(稼働中or停止中)の切り替えをコントローラへリクエスト
        const toggleStatus = async ()=>{
            const url = import.meta.env.VITE_URL_PROCESS_STATUS + '/1';
            const new_status = status.value.status ? false : true;
            const result = await axios.put(url, {flag_name: 'following_flag',
                                                    status: new_status})
                            .then(res =>{
                                getStatus();
                            });
        };


        return { status, getStatus, toggleStatus };
    }
}
</script>
