<template>
    <div class="">
        <span class="">&lt;&lt;</span>
        <span class="">&lt;</span>
        <PaginationButton v-if="show_pages.minus1 >= 1" v-bind:page="show_pages.minus1"
                          v-bind:char="String(show_pages.minus1)"
                          @clicked_page="paging" />
        <PaginationButton v-if="show_pages.minus2 >= 1" v-bind:page="show_pages.minus2"
                                                        v-bind:char="String(show_pages.minus2)"
                                                        @clicked_page="paging" />
        <PaginationButton v-if="show_pages.minus1 >= 1" v-bind:page="show_pages.minus1"
                                                        v-bind:char="String(show_pages.minus1)"
                                                        @clicked_page="paging" />
        <PaginationButton v-bind:page="show_pages.current"
                            v-bind:char="String(show_pages.current)"
                            @clicked_page="paging" />
        <PaginationButton v-if="show_pages.plus1 <= page_num" v-bind:page="show_pages.plus1"
                                                                v-bind:char="String(show_pages.plus1)"
                                                                @clicked_page="paging" />
        <PaginationButton v-if="show_pages.plus2 <= page_num" v-bind:page="show_pages.plus2"
                                                                v-bind:char="String(show_pages.plus2)"
                                                                @clicked_page="paging" />

    </div>

</template>

<script>
import {reactive} from "vue";
import PaginationButton from '../Components/PaginationButton.vue';
export default {
    components: { PaginationButton },
    props: {
        page_num: Number,
        current_page: Number,
    },
    setup(props){
        let show_pages = reactive({
            minus2: props.current_page - 2,
            minus1: props.current_page - 1,
            current: props.current_page,
            plus1: props.current_page + 1,
            plus2: props.current_page + 2,
        });

        const paging = page =>{
            console.log(page);
        }

        return { show_pages, paging };
    }
}
</script>

