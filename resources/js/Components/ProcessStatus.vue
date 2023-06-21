<template>
    <div v-if="status" class="p-process__item p-process__item--active">
        <div class="p-process__item__name">{{ process_name }}</div>
        <div class="p-process__item__detail">{{ detail }}</div>
    </div>
    <div v-else class="p-process__item">
         <div class="p-process__item__name">{{ process_name }}</div>
        <div class="p-process__item__detail">{{ detail }}</div>
    </div>
</template>

<script>
import {onBeforeMount, reactive, ref} from 'vue';

export default {
    props: {
        process_name: String,
        status: Boolean,
        detail: String,
    },
    setup(props){
        let is_active = props.status;
        const class_active = 'p-process__item--active';
        //console.log(is_active);
        // reactive({
        //     data: classNames({ 'p-process__item--active': props.status, 'p-process__item': true })
        // });
        return { is_active, class_active }
    }
}
</script>