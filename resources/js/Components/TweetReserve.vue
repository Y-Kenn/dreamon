<template>

    <div class="l-tweet_reserve">
        <div class="l-tweet_reserve__reserve">
            <div class="p-reserve">
                <textarea v-model="new_tweet.text" name="text" id="" cols="30" rows="10" class="p-reserve__text c-input"></textarea>
                <div class="p-reserve__inner">
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
            <div class="p-tweet_bar__tab">
                <div class="p-tweet_bar__tab__2tab">
                    <div @click="showReservingTweets" v-bind:class="{'p-tweet_bar__tab__2tab__item--active': show.tweets === 'reserving'}" class="p-tweet_bar__tab__2tab__item">予約中</div>
                    <div @click="showTweetedTweets" v-bind:class="{'p-tweet_bar__tab__2tab__item--active': show.tweets === 'tweeted'}" class="p-tweet_bar__tab__2tab__item">投稿済み</div>
                </div>

            </div>

            <div>
                <div v-if="show.tweets === 'reserving'" class="p-tweet_bar__inner">
                    <ReservedTweet v-for="tweet in reserved_tweets"
                                   v-bind:key="tweet.id"
                                   v-bind:id="tweet.id"
                                   v-bind:text="tweet.text"
                                   v-bind:reserved_date="tweet.reserved_date"
                                   @delete="getTweets" />
                </div>
                <div v-else class="p-tweet_bar__inner">
                    <ReservedTweet v-for="tweet in tweeted_tweets"
                                   v-bind:key="tweet.id"
                                   v-bind:id="tweet.id"
                                   v-bind:text="tweet.text"
                                   v-bind:reserved_date="tweet.reserved_date"
                                   @delete="getTweets" />
                </div>
            </div>

        </div>
    </div>




</template>

<script>
import {onBeforeMount, reactive, computed, onMounted} from 'vue';
import { useStore } from "vuex";
import flatpickr from 'flatpickr/dist/flatpickr.min.js';
import { Japanese } from 'flatpickr/dist/l10n/ja.js';
import ReservedTweet from '../Components/ReservedTweet.vue';
import moment from 'moment';

export default {
    components: {ReservedTweet},

    setup(props){
        //現在時刻より10分後以降の予約しかできないようにする
        const now_plus = moment().add(10,'minute').format('YYYY-MM-DD HH:mm');
        onMounted(()=>{
            //日時指定用のカレンダーを生成
            flatpickr('#js-datepicker', {
                locale      : Japanese,
                dateFormat  : 'Y/m/d H:i',
                defaultDate : now_plus,
                minDate     : now_plus,
                enableTime  : true,
                minuteIncrement: 1,
            });
        })

        const store = useStore();
        let reserved_tweets = computed(()=> store.state.reserved_tweets);
        let tweeted_tweets = computed(()=> store.state.tweeted_tweets);
        let new_tweet = reactive({
            reserved_date: now_plus,
            text: "",
        });
        //予約中のツイートを取得
        const getTweets = async ()=>{
            store.dispatch('getReservedTweets');
            store.dispatch('getTweetedTweets');
            store.dispatch('getProcessStatuses');
        };
        //予約ツイートのDB登録をコントローラへリクエスト
        const createTweet = async ()=>{
            const url = import.meta.env.VITE_URL_RESERVED_TWEET;
            const $datetime = document.getElementById('js-datepicker').value;
            new_tweet.reserved_date = $datetime;
            const result = await axios.post(url, new_tweet)
                            .then(res =>{
                                getTweets();
                                new_tweet.text = '';
                            });
        };

        let show = reactive({
            tweets: 'reserving',
        });
        //予約中のツイートを表示する
        const showReservingTweets = ()=>{
            console.log('reserving');
            show.tweets = 'reserving';
        };
        //投稿済みのツイートを表示する
        const showTweetedTweets = ()=>{
            console.log('tweeted');
            show.tweets = 'tweeted';
        };

        return { new_tweet, reserved_tweets, tweeted_tweets, getTweets, createTweet, show,　showReservingTweets, showTweetedTweets };
    }



}
</script>

<style lang="scss">
//日時指定用カレンダーのスタイル
@import 'flatpickr/dist/flatpickr.css';

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
