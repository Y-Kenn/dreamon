<template>
    <div class="p-profile">
        <div class="p-profile__input">
            <span class="p-profile__input__header">@</span>
            <input v-model="twitter_name.twitter_name" type="text" name="twitter_name" class="c-input">
        </div>
        <button @click="createFollowBase" class="c-button c-button--submit p-profile__submit">登録</button>
        
        <Account v-for="account in base_accounts"
                        v-bind:key="account.id"
                        v-bind:info="account"
                        v-bind:url="'http://localhost/target-base'"
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
        const getFollowBases = ()=>{
            store.dispatch('getFollowBases');
            // const url = 'http://localhost/target-base';
            // console.log('GET');
            // const result = await axios.get(url);
            // console.log(result);
            // base_accounts.data = result.data;
        }
        
        const createFollowBase = async ()=>{
            const url = 'http://localhost/target-base';
            console.log('POST');
            console.log(twitter_name);
            const result = await axios.post(url, twitter_name)
                            .then(res =>{
                                getFollowBases();
                                twitter_name.twitter_name = '';
                            });
        }
        return { base_accounts, twitter_name, getFollowBases, createFollowBase }

    }
}
</script>