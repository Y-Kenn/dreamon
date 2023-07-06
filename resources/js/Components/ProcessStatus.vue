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

export default {
    props: {
        process_name: String,
        status: Boolean,
        detail: String,
    },
    setup(props){
        let is_active = props.status;
        const class_active = 'p-process__item--active';

        return { is_active, class_active }
    }
}
</script>
