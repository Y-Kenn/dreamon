<template>
    <div class="p-keyword">
        <div class="p-keyword__inner">
            <input v-model="new_keywords.keywords" type="text" name="keywords" class="p-keyword__form c-form--text">
            <span @click="toggleNotFlag" v-bind:class="{ 'p-keyword__exclude--active': new_keywords.not_flag }" class="p-keyword__exclude c-input"><span>除外</span></span>
            <button @click="createKeywords" class="p-keyword__submit c-button c-button--submit">送信</button>
        </div>
        <Keyword v-for="keyword in keywords"
                    v-bind:key="keyword.id"
                    v-bind:id="keyword.id"
                    v-bind:not_flag="keyword.not_flag"
                    v-bind:text="keyword.keywords"
                    v-bind:url="url_like_keyword"
                    @delete="getKeywords" />
    </div>
</template>

<script>
import {onBeforeMount, reactive, computed} from 'vue';
import { useStore } from "vuex";
import Keyword from '../Components/Keyword.vue';


export default {
    components: { Keyword },
    setup(){
        const store = useStore();
        let keywords = computed(()=> store.state.like_keywords);
        const new_keywords = reactive({
            keywords: "",
            not_flag: false,
        });
        //いいねキーワード一覧を取得
        const getKeywords = async ()=>{
            store.dispatch('getLikeKeywords');
        };
        //除外キーワードに指定するかどうかのフラグを切り替える
        const toggleNotFlag = ()=>{
            new_keywords.not_flag = (new_keywords.not_flag) ? false : true;
        };
        const url_like_keyword = import.meta.env.VITE_URL_LIKE_KEYWORDS;
        //キーワードのDB登録をコントローラへリクエスト
        const createKeywords = async ()=>{
            new_keywords.keywords = new_keywords.keywords.replace(/　/g, ' ');
            const result = await axios.post(url_like_keyword, new_keywords)
                            .then(res =>{
                                getKeywords();
                                new_keywords.keywords = "";
                                new_keywords.not_flag = false;
                            });
        }
        return { keywords, new_keywords, createKeywords, getKeywords,toggleNotFlag, url_like_keyword };
    }
}
</script>
