<template>
    <div class="p-profile">
        <div class="p-profile__form">
            <span class="p-profile__form__header">@</span>
            <input v-model="twitter_name.twitter_name" type="text" name="twitter_name" class="c-form--text">
        </div>
        <button @click="createFollowBase" class="c-button c-button--submit p-profile__submit">登録</button>

        <Account v-for="account in base_accounts"
                        v-bind:key="account.id"
                        v-bind:info="account"
                        v-bind:url="url_target_base"
                        @delete="getFollowBases" />
    </div>

</template>


<script>
import {onBeforeMount, reactive, computed} from 'vue';
import { useStore } from "vuex";
import Account from '../Components/Account.vue';

export default {
    components: { Account },
    setup(props){
        const store = useStore();
        let base_accounts = computed(()=> store.state.follow_bases);
        let twitter_name = reactive({
            twitter_name: '',
        });
        //フォロワー参照アカウント一覧をDBから取得
        const getFollowBases = ()=>{
            store.dispatch('getFollowBases');
        }
        const url_target_base = import.meta.env.VITE_URL_TARGET_BASE;
        //フォロワー参照アカウントをDB登録(コントローラへTwitterのユーザネームを送信)
        const createFollowBase = async ()=>{
            const result = await axios.post(url_target_base, twitter_name)
                            .then(res =>{
                                getFollowBases();
                                twitter_name.twitter_name = '';
                            });
        }
        return { base_accounts, twitter_name, getFollowBases, createFollowBase, url_target_base }

    }
}
</script>
