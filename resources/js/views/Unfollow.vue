<template>
    <div class="l-app__main">
        <div class="p-page">
            <div>
                <div class="p-page__title">
                <div><i class="p-page__icon u-font_color--lightpurple c-icon--shadow fa-solid fa-user-plus"></i>アンフォロー</div>
                </div>
                <div class="p-page__discription">
                    <p>フォローが5000人を超えている場合に自動でアンフォローします。<br>
                    保護アカウントに登録したアカウントはアンフォローしません。</p>

                </div>
            </div>

            <div class="p-activate">
                <button @click="toggleStatus" v-bind:class="{ 'p-activate__button--active': status.status }" class="c-button p-activate__button">{{ (status.status) ? '自動アンフォロー中' : '自動アンフォロー開始' }}</button>
            </div>


            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> 保護アカウント
            </div>
            <UnfollowProtect />
        </div>

    </div>
</template>

<script>
import { onBeforeMount ,reactive, computed } from 'vue';
import { useStore } from "vuex";
import UnfollowProtect from '../Components/UnfollowProtect.vue';


export default {
    components: { UnfollowProtect },
    setup(){
        const store = useStore();
        let status = computed(()=> store.state.process_statuses[1]);
        //自動アンフォローの稼働状況(稼働中or停止中)を取得
        const getStatus = async ()=>{
            store.dispatch('getProcessStatuses');
        };
        //稼働状況(稼働中or停止中)の切り替えをコントローラへリクエスト
        const toggleStatus = async ()=>{
            const url = import.meta.env.VITE_URL_PROCESS_STATUS + '/1';
            const new_status = status.value.status ? false : true;
            const result = await axios.put(url, {flag_name: 'unfollowing_flag',
                                                    status: new_status})
                            .then(res =>{
                                getStatus();
                            });
        };
        return { status, getStatus, toggleStatus };
    }
}
</script>
