<template>

    <div class="l-tweet_reserve">
        <div class="l-tweet_reserve__reserve">
            <div class="p-reserve">
                <textarea v-model="new_tweet.text" name="text" id="" cols="30" rows="10" class="p-reserve__text c-input"></textarea>
                <div class="p-reserve__inner">

<!--                        <input type="datetime-local" name="reserved_date" v-model="new_tweet.reserved_date" v-bind:min="now" max="2099-12-31T23:59" class="p-reserve__datetime">-->
                    <div>
                        <label for="js-datepicker"><i class="p-reserve__icon fa-solid fa-calendar-days"></i></label>
                        <input type="text" class="p-reserve__datetime" name="datepicker" id="js-datepicker">
                        <span v-bind:class="{ 'p-reserve__text_count--over': new_tweet.text.length > 140 }" class=p-reserve__text_count>文字数 : {{ new_tweet.text.length}}</span>
                    </div>
                    <button @click="createTweet" class="p-reserve__submit c-button c-button--submit">登録</button>

                </div>

            </div>
        </div>
        <div class="l-tweet_reserve__tweets">
            <div class="p-tweet_bar__inner">
                <ReservedTweet v-for="tweet in reserved_tweets"
                                v-bind:key="tweet.id"
                                v-bind:id="tweet.id"
                                v-bind:text="tweet.text"
                                v-bind:reserved_date="tweet.reserved_date"
                                @delete="getTweets" />
            </div>
        </div>
    </div>




</template>

<script>
import {onBeforeMount, reactive, computed, onMounted} from 'vue';
import { useStore } from "vuex";
import flatpickr from 'flatpickr/dist/flatpickr.min.js';
import { Japanese } from "flatpickr/dist/l10n/ja.js"
import ReservedTweet from '../Components/ReservedTweet.vue';

export default {
    components: {ReservedTweet},

    setup(props){
        onMounted(()=>{
            flatpickr('#js-datepicker', {
                locale      : Japanese,
                dateFormat  : 'Y/m/d H:i',
                defaultDate : new Date(),
                minDate     : new Date(),
                enableTime  : true,
            });
        })

        const store = useStore();
        let reserved_tweets = computed(()=> store.state.reserved_tweets);
        let new_tweet = reactive({
            reserved_date: new Date(),
            text: "",
        });
        const getTweets = async ()=>{
            store.dispatch('getReservedTweets');
            store.dispatch('getProcessStatuses');
        };
        const createTweet = async ()=>{
            const url = import.meta.env.VITE_URL_RESERVED_TWEET;
            console.log(new_tweet);
            const $datetime = document.getElementById('js-datepicker').value;
            new_tweet.reserved_date = $datetime;
            const result = await axios.post(url, new_tweet)
                            .then(res =>{
                                getTweets();
                                new_tweet.text = '';
                            });
        };
        return { new_tweet, reserved_tweets, getTweets, createTweet };
    }



}
</script>

<style lang="scss">

@import './node_modules/flatpickr/dist/flatpickr.css';

$red        : #f00;
$blue       : #25bdcf;

/* 日曜日：赤 */
.flatpickr-calendar .flatpickr-innerContainer .flatpickr-weekdays .flatpickr-weekday:nth-child(7n + 1),
.flatpickr-calendar .flatpickr-innerContainer .flatpickr-days .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):nth-child(7n + 1) {
    color: $red;
}

/* 土曜日：青 */
.flatpickr-calendar .flatpickr-innerContainer .flatpickr-weekdays .flatpickr-weekday:nth-child(7),
.flatpickr-calendar .flatpickr-innerContainer .flatpickr-days .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):nth-child(7n) {
    color: $blue;
}

/* 祝日 */
.flatpickr-day.is-holiday{
    background: lighten($red, 40%) !important;
}

/* 入力欄の文字列を選択させないようにしておく  */
.flatpickr-calendar .numInput{
    user-select: none;
}


</style>
