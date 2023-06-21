<template>
    <div class="p-tweet_bar__item u-bg_color--white--impotant">
        <div class="p-tweet_bar__item__date_header">
            {{ date }}
            
            <i @click="deleteReservedTweet" class="p-tweet_bar__item__delete fa-solid fa-trash-can"></i>
        </div>
        <div class="c-account__header">
            <img :src="active_account.profile_image_url" alt="icon" class="c-account__header__icon">
            <div class="c-account__inner">
                <div class="c-account__header__name">
                    {{ active_account.name }}
                </div>
                <div class="c-account__header__screen_name">
                    {{ active_account.username }}
                </div>
            </div>
        </div>
        <div class="p-tweet_bar__item__text">
            <pre>{{ text }}</pre>
        </div>
    </div>
</template>

<script>
import { onBeforeMount ,reactive, computed } from 'vue';
import { useStore } from "vuex";
import moment from 'moment';

export default {
    props: {
        id: Number,
        text: String,
        reserved_date: String
    },
    emits: ['delete'],
    setup(props, context){
        const store = useStore();
        let active_account = computed(()=> store.state.active_account);
        const date = moment(props.reserved_date).format('YYYY/M/D HH:mm');
        console.log(props.reserved_date);
        const deleteReservedTweet = async ()=>{
            const url = 'http://localhost/reserved-tweet'+ '/' + props.id;
            console.log('Delete');
            const result = await axios.delete(url, {
                                        data: props.id})
                                        .then(res =>{
                                            context.emit('delete');
                                        });
        };
        return { active_account, date, deleteReservedTweet };
    }
}
</script>