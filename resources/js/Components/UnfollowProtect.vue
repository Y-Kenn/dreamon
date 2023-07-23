<template>
    <div class="p-profile">
        <div class="p-profile__form">
            <span class="p-profile__form__header">@</span>
            <input v-model="twitter_name.twitter_name" type="text" name="twitter_name" class="c-form--text">
        </div>
        <button @click="createProtectedAccount" class="p-profile__submit c-button c-button--submit">送信</button>

        <Account v-for="account in protected_accounts"
                        v-bind:key="account.record_id"
                        v-bind:info="account"
                        v-bind:url="url_protected_account"
                        @delete="getProtectedAccounts" />
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
        let protected_accounts = computed(()=> store.state.protected_accounts);
        let twitter_name = reactive({
            twitter_name: '',
        });
        //保護アカウントの一覧取得
        const getProtectedAccounts = async ()=>{
            store.dispatch('getProtectedAccounts');
        }
        const url_protected_account = import.meta.env.VITE_URL_PROTECTED_ACCOUNT;
        //保護アカウントの登録をコントローラへリクエスト
        const createProtectedAccount = async ()=>{
            const result = await axios.post(url_protected_account, twitter_name)
                            .then(res =>{
                                twitter_name.twitter_name = '';
                                getProtectedAccounts();
                            });
        }

        return { protected_accounts, twitter_name, getProtectedAccounts, createProtectedAccount, url_protected_account }

    }
}
</script>
