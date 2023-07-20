<template>
    <div class="p-profile__item c-account">
        <div class="p-profile__item__header">
            <div class="c-account__header">
                <a v-bind:href="account_url" target="_new">
                    <img v-bind:src="info.profile_image_url" alt="" class="c-account__header__icon">
                </a>
                <div class="c-account__inner">
                    <h3 class="c-account__header__name">{{ info.name }}</h3>
                    <h3 class="c-account__header__screen_name">{{ info.username }}</h3>
                </div>
            </div>
            <div v-if="info.active_flag" class="p-profile__item__status p-profile__item__status--active">
                active
            </div>
            <div v-else class="p-profile__item__status p-profile__item__status--inactive">
                <button @click="changeActiveAccount">切り替える</button>
            </div>
        </div>


         <span class="p-profile__item__description c-account__description">
            {{ info.description }}
        </span>
        <div class="p-profile__item__ff c-account__inner">
            <div class="p-profile__item__data">
                <span>フォロワー：</span>
                <span>{{ info.public_metrics.followers_count }}</span>
            </div>
            <div class="p-profile__item__data">
                <span>フォロー　：</span>
                <span>{{ info.public_metrics.following_count }}</span>
            </div>
            <div class="p-profile__item__data">
                <span>FF比　：</span>
                <span>{{ Math.round((info.public_metrics.followers_count / info.public_metrics.following_count) * 100) / 100 }}</span>
            </div>
        </div>
        <div class="p-profile__item__delete">
            <i v-if="!info.active_flag" @click="deleteAccount" class="fa-solid fa-xmark"></i>
        </div>
    </div>
</template>


<script>
import { useStore } from "vuex";

export default {
    props: {
        info: Object,
        url: String
    },
    setup(props, context){
        const store = useStore();
        const account_url = 'https://twitter.com/' + props.info.username;
        //操作するアカウントの切り替えをコントローラへリクエスト
        const changeActiveAccount = async ()=>{
            const url = props.url+ '/' + props.info.record_id;
            const result = await axios.put(url, {active_flag: true})
                                        .then(res =>{
                                            context.emit('put');
                                        });
        };
        //Kamitter内でのTwitterアカウント削除
        const deleteAccount = async ()=>{
            const url = import.meta.env.VITE_URL_TWITTER_REGISTER + '/' + props.info.record_id;
            const confirm_delete = confirm('OKを押すとこのTwitterアカウントに関するKamitter内での全ての情報が削除されます。');
            if(confirm_delete){
                console.log(url)
                const result = await axios.delete(url)
                                        .then(res =>{
                                            context.emit('delete');
                                        });
            }

        };

        return { account_url, changeActiveAccount, deleteAccount };
    }
}
</script>
