<template>
    <div class="l-app__main">
        <div class="l-auth">
            <div class="p-auth">
                <h1 class="p-auth__title">退会</h1>
                <p>退会を押すと全てのデータが削除されます。</p>
                <div class="p-auth__form">
                    <router-link to="/setting" class="p-auth__form__submit">キャンセル</router-link>
                    <button @click="withdraw" class="p-auth__form__thin u-margin--top--50px">退会する</button>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
import { useStore } from "vuex";

export default {
    setup(){
        const store = useStore();
        const withdraw = async ()=>{
            console.log('click');
            const confirm_withdrow = confirm('退会すると全てのデータが削除されます。よろしいですか？');

            if(confirm_withdrow){
                const url = import.meta.env.VITE_URL_WITHDROW + '/' + store.state.user_id;
                const result = await axios.delete(url)
                    .then(res =>{
                        window.location.href = import.meta.env.VITE_URL_LOGIN;
                    });
            }
        }
        return { withdraw }
    }
}
</script>
