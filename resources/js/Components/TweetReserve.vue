<template>

    <div class="l-tweet_reserve">
        <div class="l-tweet_reserve__reserve">
            <div class="p-reserve">
                <textarea v-model="new_tweet.text" name="text" id="" cols="30" rows="10" class="p-reserve__text c-input"></textarea>
                <div class="p-reserve__inner">
                    <div>
                        <input type="datetime-local" name="reserved_date" v-model="new_tweet.reserved_date" v-bind:min="now" max="2099-12-31T23:59" class="p-reserve__datetime">
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
import { onBeforeMount ,reactive, computed } from 'vue';
import { useStore } from "vuex";
import moment from 'moment';
import ReservedTweet from '../Components/ReservedTweet.vue';

//let now = moment().format('YYYY-MM-DDThh:mm')
export default {
    components: {ReservedTweet},
    
    setup(props){
        const store = useStore();
        const now = moment().add(10,'minute').format('YYYY-MM-DDTHH:mm');
        let reserved_tweets = computed(()=> store.state.reserved_tweets);
        let new_tweet = reactive({
            reserved_date: now,
            text: "",
        });

        const getTweets = async ()=>{
            store.dispatch('getReservedTweets');
            store.dispatch('getProcessStatuses');
            // const url = 'http://localhost/reserved-tweet';
            // console.log('GET');
            
            // const result = await axios.get(url);
            
            // reserved_tweets.data = result.data;
            // console.log(reserved_tweets.data);
        };
        const createTweet = async ()=>{
            const url = 'http://localhost/reserved-tweet';
            console.log(new_tweet);
            const result = await axios.post(url, new_tweet)
                            .then(res =>{
                                getTweets();
                                new_tweet.text = '';
                            });
        };
        return { now, new_tweet, reserved_tweets, getTweets, createTweet };
    }
    

    
}
</script>