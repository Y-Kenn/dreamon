<template>
    <div class="p-setting">
        <div v-if="error.email" class="p-setting__error">
            <span>{{error.email}}</span>
        </div>
        <div class="p-setting__email">
            <input v-model="email.email" type="text" name="" class="p-setting__form c-form--text">
            <button @click="updateEmail" class="p-setting__submit c-button c-button--submit">登録</button>
        </div>


    </div>

</template>

<script>
import {onBeforeMount, reactive, ref } from 'vue';
import { useStore } from "vuex";

export default {
    setup(props, context){
        const store = useStore();
        let email = reactive({
            email: store.state.email_address,
        });
        let error = reactive({
            email: '',
        });
        //メールアドレスのDB登録をコントローラへリクエスト
        const updateEmail = async ()=>{
            const url = import.meta.env.VITE_URL_EMAIL;
            let result = await axios.put(url + '/1', email)
                                    .then(res =>{
                                        store.dispatch('getEmailAddress');
                                        error.email = '';
                                        context.emit('successRegist');
                                    }).catch(res=>{
                                            error.email = '有効なメールアドレスを入力してください';

                                    });

        }
        onBeforeMount(()=>{
            //登録済みのメールアドレスを表示するため、ストアから変数に格納
           email.email = store.state.email_address;
        });

        return { email, error, updateEmail }
    }
}
</script>
