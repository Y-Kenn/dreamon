<template>
    <div class="l-app__main">
        <div class="p-page">
            <div class="p-page__title">
                <div><i class="p-page__icon u-font_color--mainblue c-icon--shadow fa-solid fa-comment-medical"></i>ツイート予約</div>
            </div>
            <div class="p-page__discription">
                
            </div>
            <TweetReserve />

        </div>
        
    </div>
</template>

<script>
import { onBeforeMount ,reactive } from 'vue';
import TweetReserve from '../Components/TweetReserve.vue';
import { useStore } from "vuex";

export default {
    components: { TweetReserve },

}
</script>