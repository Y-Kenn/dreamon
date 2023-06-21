<template>
    <div class="l-app__main">
        <div class="p-page">
            <div class="p-page__title">
                <i class="p-page__icon c-icon--shadow fa-solid fa-gear"></i>設定
            </div>
            <div class="p-page__discription">
                <p>メールアドレスとパスワードを登録することで、Twitterアカウントの認証無しでログインできるようになります。<br>
                また自動フォローの完了通知を受け取ることができます。</p>
            </div>
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> メールアドレス
            </div>
            <MailAddress />
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> パスワード
            </div>
            <PasswordUpdate v-if="password_exist_flag" />
            <Password v-else
                        @firstRegist="checkExistPassword()" />
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> その他
            </div>
            <div class="p-page__discription">
                <span>退会する</span>
            </div>
        </div>
    </div>
</template>

<script>
import MailAddress from '../Components/MailAddress.vue';
import Password from '../Components/Password.vue';
import PasswordUpdate from '../Components/PasswordUpdate.vue';
import {onBeforeMount, ref, reactive, computed} from 'vue';
import { useStore } from "vuex";

export default {
    components: { MailAddress, Password, PasswordUpdate },
    setup(){
        const store = useStore();
        let password_exist_flag = computed(()=> store.state.password_exist_flag);
        const checkExistPassword = ()=>{
            store.dispatch('checkExistPassword');
        }
        // onBeforeMount(()=>{
        //    checkExistPassword();
        //    store.commit('setActivePage', 6);
        // });

        return { password_exist_flag, checkExistPassword }
    }
    
}
</script>