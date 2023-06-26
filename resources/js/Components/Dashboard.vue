<template>
    <div class="p-dashboard">
        
        <div class="p-dashboard__header"><span class="u-font_size--xl">T</span>ODAY'S PARFORMANCE</div>
        <div class="p-dashboard__inner">
            
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-users"></i>増加フォロワー
                </div>
                <div class="p-dashboard__item__num">{{ performances.followers_today }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-user-check"></i>フォロー
                </div>
                <div class="p-dashboard__item__num">{{ performances.following_today }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-user-slash"></i>アンフォロー
                </div>
                <div class="p-dashboard__item__num">{{ performances.unfollowing_today }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-heart-circle-check"></i>いいね
                </div>
                <div class="p-dashboard__item__num">{{ performances.like_today }}</div>
            </div>
        </div>
    </div>
    <div class="p-dashboard">
        <div class="p-dashboard__header"><span class="u-font_size--xl">30</span>&nbsp;DAY'S PARFORMANCE</div>
        <div class="p-dashboard__inner">
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-users"></i>増加フォロワー
                </div>
                <div class="p-dashboard__item__num">{{ performances.followers_30days }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-user-check"></i>フォロー
                </div>
                <div class="p-dashboard__item__num">{{ performances.following_30days }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-user-slash"></i>アンフォロー
                </div>
                <div class="p-dashboard__item__num">{{ performances.unfollowing_30days }}</div>
            </div>
            <div class="p-dashboard__item">
                <div class="p-dashboard__item__titele">
                    <i class="p-dashboard__item__icon fa-solid fa-heart-circle-check"></i>いいね
                </div>
                <div class="p-dashboard__item__num">{{ performances.like_30days }}</div>
            </div>
        </div>
    </div>

</template>

<script>
import {onBeforeMount, reactive, computed } from 'vue';
import { useStore } from "vuex";

export default {
    setup(){
        const store = useStore();
        const performances = computed(()=> store.state.performances);
        //     data: {
        //         following_today: 0,
        //         following_30days: 0,
        //         followers_today: 0,
        //         followers_30days: 0,
        //         unfollowing_today: 0,
        //         unfollowing_30days: 0,
        //         like_today: 0,
        //         like_30days: 0,
        //     }
        // });
        const getTwitterData = async ()=>{
            store.dispatch('getPerformances');
        //     const url = 'http://localhost/twitter-data';
        //     const result = await axios.get(url);
        //     performances.data = result.data;
        };
        // onBeforeMount(()=>{
        //     getTwitterData();
        // });
        return { performances }
    },
}
</script>