<template>
    <div class="p-sidenav">
        <div class="p-sidenav__header">
            <div class="p-sidenav__logo">
                <img src="https://dreamon-s3-1.s3.ap-northeast-1.amazonaws.com/logo.png" alt="logo">
            </div>
            <div @click="slideSidenav" :class="{'p-sidenav__header__triggar--active': sidenav.show}" class="p-sidenav__header__triggar">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div :class="{'p-sidenav__nav--active': sidenav.show}" class="p-sidenav__nav">
            <div class="p-sidenav__account">
                <div class="c-account">
                    <div class="c-account__header">
                        <img @click="displayInfo" :src="active_account.profile_image_url" alt="icon" class="c-account__header__icon">
                        <div class="c-account__inner">
                            <div class="c-account__header__name">
                                {{ active_account.name }}
                            </div>
                            <div class="c-account__header__screen_name">
                                {{ active_account.username }}
                            </div>
                        </div>

                    </div>
                </div>
                <div class="p-sidenav__info">
                    <div class="p-sidenav__info__title">フォロー</div>
                    <div class="p-sidenav__info__num">{{ active_account.public_metrics.following_count}}</div>
                </div>
                <div class="p-sidenav__info">
                    <div class="p-sidenav__info__title">フォロワー</div>
                    <div class="p-sidenav__info__num">{{ active_account.public_metrics.followers_count}}</div>
                </div>
            </div>
            <div class="p-sidenav__menu">
                <router-link @click="slideSidenav" to="/home" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-house u-margin--right--10px"></i>ホーム</router-link>
                <router-link @click="slideSidenav" to="/follow" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-user-check u-margin--right--10px"></i>フォロー</router-link>
                <router-link @click="slideSidenav" to="/unfollow" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-user-large-slash u-margin--right--10px"></i>アンフォロー</router-link>
                <router-link @click="slideSidenav" to="/like" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-heart-circle-check u-margin--right--10px"></i>いいね</router-link>
                <router-link @click="slideSidenav" to="/tweet" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-comment-medical u-margin--right--10px"></i>ツイート予約</router-link>
                <router-link @click="slideSidenav" to="/twitter-account" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-right-left u-margin--right--10px"></i>アカウント切り替え</router-link>
                <router-link @click.prevent.stop="slideSidenav" to="/setting" active-class="p-sidenav__item--active" class="p-sidenav__item"><i class="fa-solid fa-gear u-margin--right--10px"></i>設定</router-link>
                <!-- <a href="" class="p-sidenav__item"><i class="fa-solid fa-circle-question u-margin--right--10px"></i>ヘルプ</a> -->
                <a @click.prevent.stop="logout" href="" class="p-sidenav__item"><i class="fa-solid fa-right-from-bracket u-margin--right--10px"></i>ログアウト</a>
            </div>
        </div>
    </div>

</template>


<script>
import { onBeforeMount, reactive, computed } from 'vue';
import { useStore } from "vuex";

export default {
    setup(){
        const store = useStore();
        const active_account = computed(()=> store.state.active_account);
        const my_accounts = computed(()=> store.state.my_accounts);
        const getActiveAccount = ()=>{
            store.dispatch('getMyAccounts');
        }
        const logout = async ()=>{
            const url = import.meta.env.VITE_URL_LOGOUT;
            let result = await axios.post(url)
                                .then(res =>{
                                    window.location.href = import.meta.env.VITE_URL_LOGIN;
                                });
            console.log(result);
        }
        const displayInfo = ()=>{
            console.log(my_accounts);
        }
        let sidenav = reactive({
            show: false
        });
        const slideSidenav = ()=>{
            sidenav.show = !sidenav.show;
            console.log('click');
        };
        // onBeforeMount(()=>{
        //     getActiveAccount();
        //     console.log('sidenav');
        // });
        return {active_account, my_accounts, logout, displayInfo, sidenav, slideSidenav }
    }
}
</script>
