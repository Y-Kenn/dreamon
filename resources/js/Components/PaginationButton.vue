<template>
    <span @click="notifyClickedPage" class="">{{ char }}</span>
</template>

<script>
export default {
    props:{
        page: Number,
        char: String,
    },
    setup(props, context){
        const notifyClickedPage = ()=>{
            context.emit('clicked_page', props.page);
        };

        return { notifyClickedPage };
    }

}
</script>

