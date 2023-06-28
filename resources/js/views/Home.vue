<template>
    
    <div class="l-app__main">
        <div class="p-home">
            <div class="l-dashboard">
                

                <div class="l-dashboard__main">
                    <div class="p-home__header">
                        <i class="p-home__icon u-font_color--mainblue c-icon--shadow fa-solid fa-square-poll-vertical"></i>ダッシュボード
                    </div>
                    <Dashboard /> 
                    <div class="p-home__header u-margin--top--10px">
                        <i class="p-home__icon u-font_color--mainblue c-icon--shadow fa-regular fa-circle-play"></i>稼働状況
                    </div>
                    <div v-if="!locked_flag" class="p-process">
                        <ProcessStatus v-for="process in process_statuses"
                                        v-bind:key="process.id"
                                        v-bind:process_name="process.process_name"
                                        v-bind:status="process.status"
                                        v-bind:detail="process.detail" />
                    </div>

                    <div v-else class="p-process">
                        <div @click="updateLockedFlag" class="p-process__item p-process__item--danger c-button">
                            <div class="p-process__item__name ">
                                <i class="c-icon--margin fa-solid fa-triangle-exclamation"></i>Twitterアカウントが凍結されている可能性があります
                            </div>
                            <div class="p-process__item__detail">アカウントが凍結されていないことを確認後、こちらをクリックして再開してください。</div>
                            
                        </div>
                    </div>
                    
                </div>
                
                
                
                <div class="l-dashboard__sub">
                    <div class="p-home__header">
                        <i class="p-home__icon u-font_color--mainblue c-icon--shadow fa-solid fa-message"></i>メンション
                    </div>
                    <MentionBar />
                </div>
            </div>    
        </div>
        
        
    </div>

</template>

<script>
import {onBeforeMount, reactive, computed} from 'vue';
import Dashboard from '../Components/Dashboard.vue';
import ProcessStatus from '../Components/ProcessStatus.vue';
import MentionBar from '../Components/MentionBar.vue';
import { useStore } from "vuex";

export default {
    
    components: { Dashboard, ProcessStatus, MentionBar },
    setup(){
        const store = useStore();
        const process_statuses = computed(()=> store.state.process_statuses);
        let locked_flag = computed(()=> store.state.locked_flag);
        const getProcessStatuses = ()=>{
            store.dispatch('getProcessStatuses');
        };
        const updateLockedFlag = async ()=>{
            const update_confirm = confirm('Twitterアカウントが凍結されていないことを確認しましたか？');
            if(update_confirm){
                const url = import.meta.env.VITE_URL_LOCKED + '/1'
                const result = await axios.put(url, {locked_flag: false})
                                .then(res =>{
                                    store.dispatch('getLockedFlag');
                                });
            }
        }
        onBeforeMount(()=>{
            store.dispatch('getLockedFlag');
        })
        
        return { process_statuses, locked_flag, updateLockedFlag }
        
    }
}
</script>