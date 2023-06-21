<template>
    <div class="p-setting__password">
        <span class="p-setting__password__name">現在のパスワード</span>
        <input v-model="password.current_password" id="current_password" name="current_password" type="password" class="p-setting__password__input c-input" autocomplete="current-password">
    </div>
    <div v-if="error.current_password" class="p-setting__error">
        <span>{{error.current_password}}</span>
    </div>
    <div class="p-setting__password">
        <span class="p-setting__password__name">新しいパスワード</span>
        <input v-model="password.password" id="password" name="password" type="password" class="p-setting__password__input c-input" autocomplete="new-password">
    </div>
    <div v-if="error.password" class="p-setting__error">
        <span>{{error.password}}</span>
    </div>
    <div class="p-setting__password">
        <span class="p-setting__password__name">新しいパスワード(確認)</span>
        <input v-model="password.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" class="p-setting__password__input c-input" autocomplete="new-password">    
        <button @click="updatePassword" class="p-setting__submit c-button--submit">登録</button>
    </div>
    <div v-if="error.password_confirmation" class="p-setting__error">
        <span>{{error.password_confirmation}}</span>
    </div>
    
    
    
    
</template>

<script>
import {onBeforeMount, reactive} from 'vue';
// import { Field, Form, ErrorMessage } from 'vee-validate';

export default {
    // components: { Field, Form, ErrorMessage },
    setup(){
        let password = reactive({
            current_password: '',
            password: '',
            password_confirmation: '',
        });
        let error = reactive({
            current_password: '',
            password: '',
            password_confirmation: '',
        });
        let message = reactive({
            error01: 'パスワードを入力してください',
            error02: 'パスワードが間違っています',
            error03: '確認用パスワードが一致しません',
            error04: '半角英数字記号で入力してください',
            error05: '6文字以上、20文字以下で入力してください',
        });
        const checkPassword = ()=>{
            if(password.current_password === ''){
                error.current_password = message.error01;
                return false;
            }else{
                error.current_password = '';
            }
            if(password.password === ''){
                error.password = message.error01;
                return false;
            }else{
                error.password = '';
            }
            if(password.password_confirmation === ''){
                error.password_confirmation = message.error01;
                return false;
            }else{
                error.password_confirmation = '';
            }

            if(password.password.length < 6 || password.password.length > 20){
                error.password = message.error05;
                return false;
            }else{
                error.password = '';
            }

            if(!password.password.match(/^[!-~]+$/)){
                error.password = message.error04;
            }else{
                error.password = '';
            }
            
            if(password.password === password.password_confirmation){
                error.password_confirmation = '';
                return true;
            }else{
                error.password_confirmation = message.error03;
                return false;
            }
        }
        const updatePassword = async ()=>{
            if(!checkPassword()){
                console.log('not match');
                return false;
            }else{
                error.password_confirmation = '';
                console.log('match');
            }
            console.log(password);
            const url = 'http://localhost/regist-password/1';
            let result = await axios.put(url, password)
                            .then(res =>{
                                password.current_password = '';
                                password.password = '';
                                password.password_confirmation = '';
                            }).catch(res=>{
                                if(res.message === 'validation.current_password'){
                                    error.current_password = message.error02;
                                }
                            });
            
            
        };
        return { password, error, updatePassword };
    }
}
</script>