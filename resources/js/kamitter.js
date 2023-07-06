import './bootstrap';


import { createApp, h } from 'vue';
import store from './store';

import App from './App.vue';
import router from './router';
import { axiosErrorHandle } from './axiosErrorHandler'



axiosErrorHandle();

const app = createApp(App);
app.use(router).use(store).mount('#app');


const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';


