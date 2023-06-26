<template>
    <div class="p-tweet_bar">
            
            <SmallTweet v-for="mention in mentions"
                        v-bind:key="mention.id"
                        v-bind:tweet="mention" />
    </div>
    
</template>

<script>
import {onBeforeMount, reactive, computed} from 'vue';
import { useStore } from "vuex";
import SmallTweet from '../Components/SmallTweet.vue';


export default {
    components: { SmallTweet },
    setup(){
        const store = useStore();
        const mentions = computed(()=> store.state.mentions);
        // const getMentions = async ()=>{
        //     const url = 'http://localhost/mention';
        //     const result = await axios.get(url);
        //     mentions.data = result.data;
        //     console.log(mentions);
        // };
        // onBeforeMount(()=>{
        //     getMentions();
        // });
        return { mentions }
    }
}
</script>