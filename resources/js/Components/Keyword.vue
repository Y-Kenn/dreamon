<template>
    <div v-if="not_flag" class="p-keyword__item u-bg_color--lightGray">
        <i v-if="not_flag" class="fa-solid fa-ban"></i>
        {{ text }} <i @click="deleteKeywords" class="fa-solid fa-xmark p-keyword__delete"></i>
    </div>
    <div v-else class="p-keyword__item">
        {{ text }} <i @click="deleteKeywords" class="fa-solid fa-xmark p-keyword__delete"></i>
    </div>
    
</template>

<script>
export default {
    props: {
        id: Number,
        text: String,
        not_flag: Number,
        url: String
    },
    setup(props, context){
        const deleteKeywords = async ()=>{
            const url = props.url + '/' + props.id;
            console.log('Delete');
            console.log(url);
            const result = await axios.delete(url, {
                                        data: props.id})
                                        .then(res =>{
                                            context.emit('delete');
                                        });
        };
        return { deleteKeywords };
    }
}
</script>