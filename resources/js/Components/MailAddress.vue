<template>
    <div class="p-setting">
        <div class="p-setting__email">
            <input v-model="email.email" type="text" name="" class="p-setting__email__input c-input">
            <button @click="updateEmail" class="p-setting__submit c-button--submit">登録</button>
        </div>
        <div v-if="error.email" class="p-setting__error">
            <span>{{error.email}}</span>
        </div>
        
    </div>
    
</template>

<script>
import {onBeforeMount, reactive, ref } from 'vue';
import { useStore } from "vuex";

export default {
    setup(){
        const store = useStore();
        let email = reactive({
            email: store.state.email_address,
        });
        let error = reactive({
            email: '',
        });
        const updateEmail = async ()=>{
            const url = import.meta.env.VITE_URL_EMAIL;
            let result = await axios.put(url + '/1', email)
                                    .then(res =>{
                                        store.dispatch('getEmailAddress');
                                    }).catch(res=>{
                                            console.log(res);
                                            error.email = '有効なメールアドレスを入力してください';
                                            
                                    });

        }
        onBeforeMount(()=>{
           email.email = store.state.email_address;
           console.log('store')
           console.log(store.state.email_address)
        });
        return { email, error, updateEmail }
    }
}
</script>