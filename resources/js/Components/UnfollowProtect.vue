<template>
    <div class="p-profile">
        <div class="p-profile__input">
            <span class="p-profile__input__header">@</span>
            <input v-model="twitter_name.twitter_name" type="text" name="twitter_name" class="p-profile__input c-input">
        </div>
        <button @click="createProtectedAccount" class="p-profile__submit c-button--submit">送信</button>
        
        <Account v-for="account in protected_accounts"
                        v-bind:key="account.record_id"
                        v-bind:info="account"
                        v-bind:url="'http://localhost/protected-account'"
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
        const getProtectedAccounts = async ()=>{
            store.dispatch('getProtectedAccounts');
        }
        
        const createProtectedAccount = async ()=>{
            const url = 'http://localhost/protected-account';
            console.log('POST');
            console.log(twitter_name);
            const result = await axios.post(url, twitter_name)
                            .then(res =>{
                                twitter_name.twitter_name = '';
                                getProtectedAccounts();
                            });
        }
        
        return { protected_accounts, twitter_name, getProtectedAccounts, createProtectedAccount }

    }
}
</script>