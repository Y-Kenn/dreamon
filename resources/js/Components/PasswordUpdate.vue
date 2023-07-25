<template>
    <div class="p-setting">
        <div class="p-setting__password">
            <span class="p-setting__password__name">現在のパスワード</span>
            <input v-model="password.current_password" id="current_password" name="current_password" type="password" class="p-setting__form c-form--text" autocomplete="current-password">
        </div>
        <div v-if="error.current_password" class="p-setting__error">
            <span>{{error.current_password}}</span>
        </div>
        <div class="p-setting__password">
            <span class="p-setting__password__name">新しいパスワード</span>
            <input v-model="password.password" id="password" name="password" type="password" class="p-setting__form c-form--text" autocomplete="new-password">
        </div>
        <div v-if="error.password" class="p-setting__error">
            <span>{{error.password}}</span>
        </div>
        <div class="p-setting__password">
            <span class="p-setting__password__name">新しいパスワード(確認)</span>
            <input v-model="password.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" class="p-setting__form c-form--text" autocomplete="new-password">
            <button @click="updatePassword" class="p-setting__submit c-button c-button--submit">登録</button>
        </div>
        <div v-if="error.password_confirmation" class="p-setting__error">
            <span>{{error.password_confirmation}}</span>
        </div>
        <a :href="url_forget_password" class="p-setting__forget">パスワードを忘れた方はこちら</a>

    </div>




</template>

<script>
import {onBeforeMount, reactive} from 'vue';

export default {
    setup(props, context){
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
            error05: '8文字以上、20文字以下で入力してください',
        });
        //パスワードのバリデーション
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

            if(password.password.length < 8 || password.password.length > 20){
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
        };
        //パスワードの更新をコントローラへリクエスト
        const updatePassword = async ()=>{
            if(!checkPassword()){
                return false;
            }else{
                error.password_confirmation = '';
            }
            const url = import.meta.env.VITE_URL_REGIST_PASSWORD + '/1';
            let result = await axios.put(url, password)
                            .then(res =>{
                                password.current_password = '';
                                password.password = '';
                                password.password_confirmation = '';
                                context.emit('successRegist');
                            }).catch(res=>{
                                if(res.message === 'validation.current_password'){
                                    error.current_password = message.error02;
                                }
                            });
        };
        const url_forget_password = import.meta.env.VITE_URL_FORGET_PASSWORD;

        return { password, error, updatePassword, url_forget_password };
    }
}
</script>
