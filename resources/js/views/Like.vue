<template>
    <div class="l-app__main">
        <div class="p-page">
                <div>
                    <div class="p-page__title">
                        <div><i class="p-page__icon u-font_color--pink c-icon--shadow fa-solid fa-heart-circle-plus"></i>いいね</div>
                    </div>
                    <div class="p-page__discription">
                        <p>キーワードのいずれかにヒットするツイートを抽出して自動でいいねします。</p>
                        
                    </div>
                </div>
            <div class="p-activate">
                <button @click="toggleStatus" v-bind:class="{ 'p-activate__button--active': status.status }" class="p-activate__button">{{ (status.status) ? '自動いいね中' : '自動いいね開始' }}</button>
            </div>
            
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> キーワード
            </div>
            <div class="p-page__discription">
                <p>スペースで区切って同時に複数のキーワードを登録した場合、その全てのキーワードがヒットしたツイートのみいいねします。
                    除外指定したキーワードにヒットするツイートはいいねされません。</p>
            </div>
            <LikeKeyword />
        </div>
        
    </div>
</template>

<script>
import { onBeforeMount ,reactive, computed } from 'vue';
import LikeKeyword from '../Components/LikeKeyword.vue';
import { useStore } from "vuex";

export default {
    components: { LikeKeyword },
    setup(props){
        const store = useStore();
        let status = computed(()=> store.state.process_statuses[2]);
        const getStatus = async ()=>{
            store.dispatch('getProcessStatuses');
        };
        const toggleStatus = async ()=>{
            const url = import.meta.env.VITE_URL_PROCESS_STATUS + '/1';
            console.log(status.value.status);
            const new_status = status.value.status ? false : true;
            const result = await axios.put(url, {flag_name: 'liking_flag',
                                                    status: new_status})
                            .then(res =>{
                                getStatus();
                            });
        };
        return { status, getStatus, toggleStatus };
    }
}
</script>