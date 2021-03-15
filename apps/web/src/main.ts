import Vue from 'vue';
import App from './App.vue';
import desktopRouter from './router/desktopRouter';
import mobileRouter from './router/mobileRouter';
import store from './store';
import './registerServiceWorker';
import i18n from './i18n';
import {IS_MOBILE} from "@/helpers/Utils";
import Icons from "@/components/Icons.vue";

Vue.config.productionTip = false;
// register global components
Vue.component('Icons', Icons);

if (IS_MOBILE) {
    new Vue({
        router: mobileRouter,
        store,
        i18n,
        render: (h) => h(App)
    }).$mount('#app');

} else {
    new Vue({
        router: desktopRouter,
        store,
        i18n,
        render: (h) => h(App)
    }).$mount('#app');
}
