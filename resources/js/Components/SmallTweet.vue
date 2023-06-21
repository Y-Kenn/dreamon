<template>
    <a v-bind:href="tweet_url" target="_new" class="p-tweet_bar__item">
        <div class="c-account__header">
            <img :src="tweet.profile_image_url" alt="icon" class="c-account__header__icon">
            <div class="c-account__inner">
                <div class="c-account__header__name">
                    {{ tweet.name }}
                </div>
                <div class="c-account__header__screen_name">
                    {{ tweet.username }}
                </div>
            </div>
        </div>
        <div class="p-tweet_bar__item__text">
            {{ tweet.text }}
        </div>
        <div class="p-tweet_bar__item__date">
            {{ date }}
        </div>
    </a>
</template>

<script>
import moment from 'moment';

export default {
    props: {
        tweet: Object
    },
    setup(props){
        const date = moment(props.tweet.created_at).format('YYYY/M/D HH:mm');
        const tweet_url = 'https://twitter.com/' + props.tweet.username + '/status/' + props.tweet.id;
        return { date, tweet_url };
    }
}
</script>