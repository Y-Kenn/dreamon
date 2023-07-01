<template>
    <div class="l-app__main">
        <!--トースト-->
        <div v-if="toast.show" class="p-toast">
            <div class="p-toast__item">
                送信されました
            </div>
        </div>

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
            <MailAddress @successRegist="showToast" />
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> パスワード
            </div>
            <PasswordUpdate v-if="password_exist_flag"
                            @successRegist="showToast"/>
            <Password v-else @firstRegist="checkExistPassword()"
                            @successRegist="showToast" />
            <div class="p-page__sub_title">
                <i class="fa-solid fa-square"></i> その他
            </div>
            <div class="p-page__discription">
                <router-link to="/withdraw" class="u-font_color--link">退会はこちら</router-link>
            </div>
        </div>
    </div>
</template>

<script>
import MailAddress from '../Components/MailAddress.vue';
import Password from '../Components/Password.vue';
import PasswordUpdate from '../Components/PasswordUpdate.vue';
import {onBeforeMount, ref, reactive, computed, inject, onMounted} from 'vue';
import { useStore } from "vuex";

export default {
    components: { MailAddress, Password, PasswordUpdate },
    setup(){
        const store = useStore();
        let password_exist_flag = computed(()=> store.state.password_exist_flag);
        const checkExistPassword = ()=>{
            store.dispatch('checkExistPassword');
        };
        let toast = reactive({
            show: false
        });
        const showToast = ()=>{
            toast.show = true;
            setTimeout(()=>toast.show = false, 5500)
        }

        return { password_exist_flag, checkExistPassword, toast, showToast }
    }

}
</script>

